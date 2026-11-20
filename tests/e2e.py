import os, sqlite3, re, sys, urllib.request, urllib.parse, http.cookiejar, uuid

LOCK = os.path.join(os.path.dirname(os.path.abspath(__file__)), "tmp", "install.lock")
BASE = "http://127.0.0.1:8099"
fails = 0

def check(name, ok, detail=""):
    global fails
    print(("  ok   " if ok else "  FAIL ") + name + ("" if ok else f" – {detail}"))
    if not ok:
        fails += 1

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k):
        return None

class Client:
    def __init__(self):
        self.jar = http.cookiejar.CookieJar()
        self.op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar), NoRedirect)
        self.csrf = ""

    def req(self, path, data=None, files=None, follow=True):
        url = path if path.startswith("http") else BASE + path
        headers = {}
        body = None
        if files:
            b = uuid.uuid4().hex
            parts = []
            for k, v in (data or {}).items():
                parts.append(f'--{b}\r\nContent-Disposition: form-data; name="{k}"\r\n\r\n{v}\r\n'.encode())
            for k, (fn, content) in files.items():
                parts.append(f'--{b}\r\nContent-Disposition: form-data; name="{k}"; filename="{fn}"\r\nContent-Type: text/csv\r\n\r\n'.encode() + content.encode() + b"\r\n")
            parts.append(f"--{b}--\r\n".encode())
            body = b"".join(parts)
            headers["Content-Type"] = "multipart/form-data; boundary=" + b
        elif data is not None:
            body = urllib.parse.urlencode(data).encode()
        r = urllib.request.Request(url, data=body, headers=headers)
        try:
            resp = self.op.open(r)
            code, loc, text = resp.status, None, resp.read().decode()
        except urllib.error.HTTPError as e:
            code, loc, text = e.code, e.headers.get("Location"), e.read().decode(errors="replace")
        m = re.search(r'name="csrf" value="([0-9a-f]+)"', text)
        if m:
            self.csrf = m.group(1)
        if follow and code in (301, 302, 303) and loc and loc.startswith("/"):
            return self.req(loc, follow=True)
        return code, loc, text

    def post(self, path, data, **kw):
        d = dict(data)
        d["csrf"] = self.csrf
        return self.req(path, d, **kw)

    def login(self, email, pw):
        self.req("/admin/index.php?r=login")
        return self.post("/admin/index.php?r=login", {"email": email, "password": pw})

print("Login and roles")
lea = Client()
code, _, html = lea.login("lea@example.org", "leaderlea1")
check("leader logs in (German interface)", code == 200 and "Übersicht" in html and 'lang="de"' in html, code)
code, _, _ = lea.req("/admin/index.php?r=users")
check("leader cannot open user admin", code == 403, code)
bad = Client()
code, _, html = bad.login("lea@example.org", "wrong")
check("wrong password rejected", "alert-error" in html and "Abmelden" not in html)
throttled = Client()  # anonymous visitor without Accept-Language: English texts
for _ in range(5):
    code, _, html = throttled.login("throttle@example.org", "wrong")
check("login throttled after the 5th failure", "Too many failed attempts" in html and "in 1 minutes" in html, re.findall(r'alert-error[^>]*>(.*?)<', html))
code, _, html = throttled.login("throttle@example.org", "throttle123")
check("correct password is also refused while throttled", "class=\"logout\"" not in html and "Too many failed attempts" in html)
code, _, _ = lea.req("/admin/index.php?r=study.new", {"title": "x", "slug": "x-y", "csrf": "nope"})
check("POST without valid CSRF token rejected", code == 400, code)

print("Study setup (leader)")
lea.req("/admin/index.php?r=study.new")
code, loc, html = lea.post("/admin/index.php?r=study.new", {"title": "Akku-Studie", "slug": "akku"}, follow=False)
sid = re.search(r"id=(\d+)", loc or "").group(1)
check("study created", code == 303 and sid, loc)
lea.req(f"/admin/index.php?r=study.attributes&id={sid}")
code, _, html = lea.post(f"/admin/index.php?r=study.attributes&id={sid}", {"attributes": "Preis: 249 € | 299 € | 349 €\nMarke: A | B | C\nAkkulaufzeit: 8 h | 10 h | 14 h"})
check("attributes saved", "3 Attribute gespeichert" in html, html[:300])
lea.req(f"/admin/index.php?r=study.design&id={sid}")
code, _, html = lea.post(f"/admin/index.php?r=study.generate&id={sid}", {"method": "minoverlap", "versions": "4", "tasks": "3", "alts": "3", "seed": "11"})
check("design generated", "Design erzeugt: 4 Versionen" in html, html[:400])
check("diagnosis shown", "Tasks mit wiederholtem Level" in html)
code, _, csvtext = lea.req(f"/admin/index.php?r=study.design.csv&id={sid}")
check("design CSV download", csvtext.startswith("version,task,alt,Preis,Marke,Akkulaufzeit") and len(csvtext.strip().splitlines()) == 1 + 4 * 3 * 3, csvtext[:200])
lea.req(f"/admin/index.php?r=study.settings&id={sid}")
code, _, html = lea.post(f"/admin/index.php?r=study.settings&id={sid}", {
    "title": "Akku-Studie", "lang": "de", "question": "Welches Gerät würden Sie kaufen?", "intro": "Willkommen.\n\nBitte wählen Sie.",
    "none_option": "1", "none_label": "Keins davon", "show_progress": "1", "randomize_attributes": "1",
    "return_url": "https://ls.example.org/index.php/123456?token={id}&anouk={status}", "error_url": "",
    "release_minutes": "60", "test_prefix": "test_"})
check("settings saved", "Einstellungen gespeichert" in html, html[:300])

print("Preview")
code, _, html = lea.req("/index.php?s=akku&preview=1&restart=1")
check("preview shows intro with banner", "Vorschau" in html and "Willkommen." in html, html[:300])
anon = Client()
code, loc, _ = anon.req("/index.php?s=akku&preview=1", follow=False)
check("preview requires login", code == 303 and "login" in (loc or ""), (code, loc))

print("Draft studies are closed for respondents")
p = Client()
code, _, html = p.req("/index.php?s=akku&id=ABC123")
check("draft study shows 'nicht aktiv'", "nicht aktiv" in html, html[:200])
lea.req(f"/admin/index.php?r=study&id={sid}")
code, _, html = lea.post(f"/admin/index.php?r=study.status&id={sid}", {"to": "field"})
check("status → field", "Status: Im Feld" in html)

print("Respondent flow")
code, _, html = p.req("/index.php?s=akku&id=ABC123")
check("intro shown first", "Willkommen." in html and 'value="intro"' in html)
code, _, html = p.req("/index.php?s=akku&id=ABC123", {"action": "intro"})
check("task 1 shown", "Auswahl 1 von 3" in html and "Welches Gerät" in html and "Keins davon" in html, html[:500])
code, _, html = p.req("/index.php?s=akku&id=ABC123", {"action": "answer", "task": "1", "rt": "1234", "sw": "1280"})
check("missing choice → error message", code == 422 and "Bitte wählen" in html, code)
code, _, html = p.req("/index.php?s=akku&id=ABC123", {"action": "answer", "task": "1", "choice": "7", "rt": "1", "sw": "1"})
check("invalid choice rejected", code == 422)
code, _, html = p.req("/index.php?s=akku&id=ABC123", {"action": "answer", "task": "1", "choice": "2", "rt": "1234", "sw": "1280"})
check("task 2 after answer", "Auswahl 2 von 3" in html)
code, _, html = p.req("/index.php?s=akku&id=ABC123", {"action": "answer", "task": "1", "choice": "3"})
check("stale resubmit of task 1 is ignored", "Auswahl 2 von 3" in html)
p.req("/index.php?s=akku&id=ABC123", {"action": "answer", "task": "2", "choice": "0", "rt": "800", "sw": "390"})
code, loc, _ = p.req("/index.php?s=akku&id=ABC123", {"action": "answer", "task": "3", "choice": "1", "rt": "500", "sw": "390"}, follow=False)
check("last answer redirects to LimeSurvey with id and status", code == 303 and loc == "https://ls.example.org/index.php/123456?token=ABC123&anouk=complete", loc)
code, loc, _ = Client().req("/index.php?s=akku&id=ABC123", follow=False)
check("completed ID is sent straight back", code == 303 and "token=ABC123" in (loc or ""), loc)

print("ID leaves the address bar")
c = Client()
code, loc, _ = c.req("/index.php?s=akku&id=CLEAN1", follow=False)
check("entry with ID redirects to a clean URL", code == 303 and "id=" not in (loc or "") and "s=akku" in (loc or ""), loc)
code, _, html = c.req(loc)
check("clean URL continues by cookie", "Willkommen." in html)
code, _, html = c.req("/index.php?s=akku", {"action": "intro"})
check("posting to the clean URL works", "Auswahl 1 von 3" in html)
code, _, html = Client().req("/index.php?s=akku")
check("no cookie and no ID: start from the survey link", code == 400 and "gültige Teilnahme-ID" in html, code)
code, _, html = Client().req("/index.php?s=akku&c=1")
check("blocked cookies are explained", code == 400 and "Cookie" in html, code)

code, _, html = Client().req("/index.php?s=akku&id=bad%20id")
check("invalid ID → message", code == 400 and "gültige Teilnahme-ID" in html, code)
code, _, html = Client().req("/index.php?s=gibtsnicht&id=1")
check("unknown study → 404", code == 404)

# a few more respondents, one test ID, one drop-out
for i, ext in enumerate(["R2", "R3", "test_me"]):
    c = Client()
    c.req(f"/index.php?s=akku&id={ext}")
    c.req(f"/index.php?s=akku&id={ext}", {"action": "intro"})
    for t in (1, 2, 3):
        c.req(f"/index.php?s=akku&id={ext}", {"action": "answer", "task": str(t), "choice": str(1 + (t % 3)), "rt": "900", "sw": "1024"}, follow=False)
drop = Client()
drop.req("/index.php?s=akku&id=DROP1")
drop.req("/index.php?s=akku&id=DROP1", {"action": "intro"})
drop.req("/index.php?s=akku&id=DROP1", {"action": "answer", "task": "1", "choice": "1"})
code, _, html = drop.req("/index.php?s=akku&id=DROP1")
check("drop-out resumes at task 2", "Auswahl 2 von 3" in html)

print("Export")
code, _, csvtext = lea.req(f"/admin/index.php?r=study.export&id={sid}&scope=complete&sep=,")
lines = csvtext.strip().splitlines()
check("long export header uses variable names", lines[0] == "id,obs_id,version,task,alt,preis,marke,akkulaufzeit,none,choice,rt_ms,screen_w,answered_at,status,is_test", lines[0])
check("timestamps in ISO 8601 UTC", re.search(r",\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ,", lines[1]) is not None, lines[1])
# 3 complete non-test respondents × 3 tasks × (3 alts + none)
check("long export rows (complete, no tests)", len(lines) - 1 == 3 * 3 * 4, len(lines) - 1)
abc = [l.split(",") for l in lines[1:] if l.startswith("ABC123,")]
t2 = [r for r in abc if r[3] == "2"]
check("none choice exported on the none row", [r[9] for r in t2] == ["0", "0", "0", "1"] and t2[3][8] == "1", t2)
t1 = [r for r in abc if r[3] == "1"]
check("chosen alt marked, rt stored", [r[9] for r in t1] == ["0", "1", "0", "0"] and t1[0][10] == "1234", t1)
check("obs_id constant within a task, distinct across tasks", len({r[1] for r in abc}) == 3 and all(len({r[1] for r in abc if r[3] == str(t)}) == 1 for t in (1, 2, 3)))
check("each task has exactly one choice", all(sum(int(r[9]) for r in abc if r[3] == str(t)) == 1 for t in (1, 2, 3)))
code, _, csvtext = lea.req(f"/admin/index.php?r=study.export&id={sid}&scope=all&tests=1&sep=;")
lines = csvtext.lstrip("﻿").strip().splitlines()
check("all + tests + semicolon", lines[0].startswith("id;obs_id;version") and len(lines) - 1 == (4 * 3 + 1) * 4, len(lines) - 1)
code, _, csvtext = lea.req(f"/admin/index.php?r=study.export&id={sid}&format=wide&scope=complete")
lines = csvtext.strip().splitlines()
check("wide export: one row per completed ID", lines[0] == "id,version,status,is_test,n_answered,started_at,completed_at,choice_t01,choice_t02,choice_t03,rt_t01,rt_t02,rt_t03" and len(lines) - 1 == 3, lines[0])
abc = [l.split(",") for l in lines[1:] if l.startswith("ABC123,")][0]
check("wide export: choices incl. none (0)", abc[7:10][1] == "0" and abc[10] == "1234", abc)
code, hdr, csvtext = lea.req(f"/admin/index.php?r=study.export&id={sid}&format=codebook")
check("codebook export", csvtext.startswith("variable,label,type,values") and "\npreis,Preis," in csvtext and "choice_t03" in csvtext, csvtext[:200])
code, _, html = lea.post(f"/admin/index.php?r=study.varnames&id={sid}", {"var[0]": "x"})
check("variable names: unknown ids ignored, nothing breaks", code == 200)
code, _, html = lea.req(f"/admin/index.php?r=study.attributes&id={sid}")
ids = re.findall(r'name="var\[(\d+)\]" value="([a-z0-9_]+)"', html)
check("variable-name editor shown while in the field", [v for _, v in ids] == ["preis", "marke", "akkulaufzeit"], ids)
code, _, html = lea.post(f"/admin/index.php?r=study.varnames&id={sid}", {f"var[{ids[0][0]}]": "price_eur", f"var[{ids[1][0]}]": "brand", f"var[{ids[2][0]}]": "battery_h"})
check("variable names saved", "Variablennamen gespeichert" in html)
code, _, html = lea.post(f"/admin/index.php?r=study.varnames&id={sid}", {f"var[{ids[0][0]}]": "brand", f"var[{ids[1][0]}]": "brand", f"var[{ids[2][0]}]": "battery_h"})
check("duplicate variable names refused", "doppelt" in html)
code, _, csvtext = lea.req(f"/admin/index.php?r=study.export&id={sid}&scope=complete")
check("export uses the new names", csvtext.startswith("id,obs_id,version,task,alt,price_eur,brand,battery_h,"), csvtext[:80])
otto_v = Client()
otto_v.login("otto@example.org", "leaderotto1")
code, _, html = otto_v.post(f"/admin/index.php?r=study.varnames&id={sid}", {f"var[{ids[0][0]}]": "hacked"})
check("other leader cannot rename variables", code == 404, code)

print("Respondents page")
otto = Client()
otto.login("otto@example.org", "leaderotto1")
code, _, html = lea.req("/admin/index.php?r=respondents")
check("leader sees own respondents", "ABC123" in html and "R2" in html and "DROP1" in html)
code, _, html = lea.req("/admin/index.php?r=respondents&q=ABC")
check("search by ID", "ABC123" in html and "R2" not in html)
code, _, html = lea.req("/admin/index.php?r=respondents&status=test")
check("filter test IDs", "test_me" in html and "ABC123" not in html)
abc_id = re.search(r'respondent&amp;id=(\d+)"><code>ABC123', lea.req("/admin/index.php?r=respondents")[2]).group(1)
code, _, html = lea.req(f"/admin/index.php?r=respondent&id={abc_id}")
check("respondent details show tasks and choices", "Task 1" in html and "Wahl: B" in html and "keine gewählt" in html)
code, _, _ = otto.req(f"/admin/index.php?r=respondent&id={abc_id}")
check("other leader cannot open the respondent", code == 404, code)
code, _, html = otto.req("/admin/index.php?r=respondents")
check("other leader does not see the respondents", "ABC123" not in html)
code, _, r = otto.req(f"/admin/index.php?r=study.rscript&id={sid}")
check("other leader cannot download the R script", code == 404 and "library(cbcTools)" not in r, code)
r2_id = re.search(r'respondent&amp;id=(\d+)"><code>R2', lea.req("/admin/index.php?r=respondents")[2]).group(1)
code, _, _ = otto.post("/admin/index.php?r=respondent.delete", {"respondent_id": r2_id})
check("other leader cannot delete the respondent", code == 404, code)
lea.req(f"/admin/index.php?r=respondent&id={r2_id}")
code, _, html = lea.post("/admin/index.php?r=respondent.delete", {"respondent_id": r2_id})
check("respondent deleted", "Proband „R2“ und seine Antworten gelöscht" in html and "<code>R2</code>" not in html)

print("Separation between project leaders")
otto = Client()
otto.login("otto@example.org", "leaderotto1")
code, _, html = otto.req("/admin/index.php?r=dashboard")
check("other leader's dashboard does not list the study", "Akku-Studie" not in html)
for route in ["study", "study.settings", "study.design", "study.design.csv", "study.export", "study.attributes"]:
    code, _, _ = otto.req(f"/admin/index.php?r={route}&id={sid}")
    check(f"other leader gets 404 on {route}", code == 404, code)
otto.req("/admin/index.php?r=dashboard")
code, _, _ = otto.post(f"/admin/index.php?r=study.delete&id={sid}", {"confirm": "akku"})
check("other leader cannot delete", code == 404, code)
code, _, html = otto.req("/index.php?s=akku&preview=1")
check("other leader cannot preview", "Antworten werden nicht gespeichert" not in html and "answers are not saved" not in html, code)

admin = Client()
admin.login("admin@example.org", "adminadmin1")
code, _, html = admin.req("/admin/index.php?r=dashboard")
check("admin sees all studies with owner", "Akku-Studie" in html and "Lea Leiterin" in html)
code, _, html = admin.req(f"/admin/index.php?r=study&id={sid}")
check("admin can open leader's study", code == 200 and "Verantwortlich" in html and "Lea Leiterin" in html)

print("User management")
admin.req("/admin/index.php?r=users")
code, _, html = admin.post("/admin/index.php?r=user.new", {"name": "Neu", "email": "neu@example.org", "role": "leader"})
pw = re.search(r'class="copy-src" readonly value="([^"]+)"', html)
check("admin creates leader, password shown once", "Konto für Neu angelegt" in html and pw)
neu = Client()
code, _, html = neu.login("neu@example.org", pw.group(1))
check("new leader must change the starting password first", "starting password" in html and 'lang="en"' in html)
code, loc, _ = neu.req("/admin/index.php?r=studies", follow=False)
check("other pages lead to the password change", code == 303 and "account" in (loc or ""), (code, loc))
code, _, html = neu.post("/admin/index.php?r=account", {"current": pw.group(1), "new": "neuesPasswort1", "repeat": "neuesPasswort1"})
check("after changing it, the leader can work", "Password changed." in html)
code, _, html = neu.req("/admin/index.php?r=overview")
check("overview reachable after the change (English by default)", "Overview" in html)
code, _, html = admin.req("/admin/index.php?r=users")
check("password not shown again", "nur jetzt angezeigt" not in html)
neu_id = re.search(r'user\.edit&amp;id=(\d+)">Neu</a>', html).group(1)
admin.post("/admin/index.php?r=user.toggle", {"user_id": neu_id})
code, loc, _ = neu.req("/admin/index.php?r=dashboard", follow=False)
check("blocked user is logged out", code == 303 and "login" in (loc or ""), (code, loc))

print("Account editing and deletion")
admin.req("/admin/index.php?r=users")
otto_id = re.search(r'user\.edit&amp;id=(\d+)">Otto Other</a>', admin.req("/admin/index.php?r=users")[2]).group(1)
admin.req(f"/admin/index.php?r=user.edit&id={otto_id}")
code, _, html = admin.post(f"/admin/index.php?r=user.edit&id={otto_id}", {"name": "Otto Neu", "email": "otto@example.org", "role": "leader", "language": "en"})
check("admin edits name and language", "Konto aktualisiert" in html and "Otto Neu" in html)
otto.req("/admin/index.php?r=study.new")
otto.post("/admin/index.php?r=study.new", {"title": "Ottos Studie", "slug": "otto-studie", "lang": "en"})
admin_id = re.search(r'user\.edit&amp;id=(\d+)">Master</a>', admin.req("/admin/index.php?r=users")[2]).group(1)
admin.req(f"/admin/index.php?r=user.edit&id={otto_id}")
code, _, html = admin.post("/admin/index.php?r=user.delete", {"user_id": otto_id, "transfer_to": admin_id})
check("deletion needs the confirmation box", "Bestätigungsfeld" in html)
code, _, html = admin.post("/admin/index.php?r=user.delete", {"user_id": otto_id, "transfer_to": admin_id, "confirm": "1"})
check("account deleted, study transferred", "1 Studien wurden übertragen" in html)
code, _, html = admin.req("/admin/index.php?r=studies")
check("transferred study belongs to the admin now", "Ottos Studie" in html)
admin.req(f"/admin/index.php?r=user.edit&id={admin_id}")
code, _, html = admin.post("/admin/index.php?r=user.delete", {"user_id": admin_id, "confirm": "1"})
check("admin cannot delete own account", "eigene Konto lässt sich nicht löschen" in html)
code, _, html = admin.post(f"/admin/index.php?r=user.edit&id={admin_id}", {"name": "Master", "email": "admin@example.org", "role": "leader", "language": "de"})
check("admin cannot drop own admin role", "Administrator" in html and "Konto aktualisiert" not in html)

print("CSV upload")
lea.req("/admin/index.php?r=study.new")
code, loc, _ = lea.post("/admin/index.php?r=study.new", {"title": "Upload", "slug": "upload-test"}, follow=False)
sid2 = re.search(r"id=(\d+)", loc).group(1)
lea.req(f"/admin/index.php?r=study.design&id={sid2}")
# two versions × two tasks × two alternatives, cbcTools-style column names
csv = "respID,qID,altID,obsID,profileID,price,brand\n1,1,1,1,1,1,X\n1,1,2,1,2,2,Y\n1,2,1,2,3,1,Y\n1,2,2,2,4,2,X\n2,1,1,3,1,3,X\n2,1,2,3,2,1,Y\n2,2,1,4,3,2,Y\n2,2,2,4,4,3,X\n"
code, _, html = lea.post(f"/admin/index.php?r=study.upload&id={sid2}", {}, files={"design": ("design.csv", csv)})
check("cbcTools CSV upload accepted", "Design gespeichert: 2 Versionen × 2 Tasks × 2 Alternativen" in html, re.findall(r'<li>(.*?)</li>', html)[:5])
code, _, html = lea.post(f"/admin/index.php?r=study.upload&id={sid2}", {}, files={"design": ("bad.csv", "respID,qID,altID,price,brand\n1,1,1,9,X\n1,1,2,1,Y\n")})
check("bad CSV shows errors", "nicht gespeichert" in html and "Level „9“" in html, re.findall(r'<li>(.*?)</li>', html)[:5])

print("cbcTools script and system page")
code, _, r = lea.req(f"/admin/index.php?r=study.rscript&id={sid}&n_resp=50&n_q=6&n_alts=3&method=balanced&seed=7")
check("owner downloads cbcTools script", "library(cbcTools)" in r and "n_resp   = 50" in r and 'method   = "balanced"' in r and "set.seed(7)" in r, r[:200])

code, _, html = admin.req("/admin/index.php?r=system")
check("admin opens system page", code == 200 and "Datenbankserver" in html and "config.php löschen" in html)
code, _, _ = lea.req("/admin/index.php?r=system")
check("leader cannot open system page", code == 403, code)
code, _, html = admin.post("/admin/index.php?r=system.reset", {"confirm": "nein"})
check("config reset needs typed confirmation", "DELETE eintippen" in html)

print("Languages, help and headers")
code, _, html = lea.req("/admin/index.php?r=lang&set=en&back=" + urllib.parse.quote("/admin/index.php?r=overview"))
check("language switch to English", "Overview" in html and 'lang="en"' in html)
code, _, html = lea.req("/admin/index.php?r=studies")
check("language is stored with the account", "Studies" in html and "Copy link" in html)
code, _, html = lea.req("/admin/index.php?r=lang&set=de&back=" + urllib.parse.quote("/admin/index.php?r=studies"))
check("switch back to German", "Link kopieren" in html and 'lang="de"' in html)
code, _, html = lea.req("/admin/index.php?r=lang&set=de&back=" + urllib.parse.quote("https://evil.example/"), follow=False)
check("language switch does not redirect off-site", "evil" not in (html or ""))
code, _, html = lea.req("/admin/index.php?r=help&lang=de")
check("help in German", "Anbindung an LimeSurvey" in html and 'class="toc"' in html)
code, _, html = lea.req("/admin/index.php?r=help&lang=en")
check("help in English", "Linking with LimeSurvey" in html)
resp = urllib.request.urlopen(BASE + "/admin/index.php?r=login")
csp = resp.headers.get("Content-Security-Policy", "")
check("Content-Security-Policy without inline scripts", "default-src 'self'" in csp and "unsafe-inline" not in csp, csp)
check("no referrer leaves the site", resp.headers.get("Referrer-Policy") == "no-referrer")
pages = [lea.req(u)[2] for u in ["/admin/index.php?r=overview", f"/admin/index.php?r=study&id={sid}", f"/admin/index.php?r=study.settings&id={sid}", "/index.php?s=akku&preview=1&restart=1"]]
check("no inline scripts or style attributes on pages", all(re.search(r"<script>|<script(?![^>]*src=)[^>]*>|style=\"|on(click|submit)=", p) is None for p in pages))

print("Security regressions")
for bad in ["/\\evil.example", "/\\/evil.example", "//evil.example", "https://evil.example/", "/\t/evil.example", "/index.php\\@evil.example", "/evil.example/index.php"]:
    code, loc, _ = lea.req("/admin/index.php?r=lang&set=de&back=" + urllib.parse.quote(bad), follow=False)
    check("language switch refuses " + repr(bad), loc == "/admin/index.php?r=overview", loc)
code, loc, _ = lea.req("/admin/index.php?r=lang&set=de&back=" + urllib.parse.quote("/admin/index.php?r=study&id=1"), follow=False)
check("language switch still returns to the page", loc == "/admin/index.php?r=study&id=1", loc)
two = Client(); two.login("lea@example.org", "leaderlea1")
lea.req("/admin/index.php?r=account")
code, _, html = lea.post("/admin/index.php?r=account", {"current": "leaderlea1", "new": "leaderlea1neu", "repeat": "leaderlea1neu"})
check("password change keeps this session", "Passwort geändert" in html and lea.req("/admin/index.php?r=studies")[0] == 200)
code, loc, _ = two.req("/admin/index.php?r=studies", follow=False)
check("password change ends the other sessions", code == 303 and "login" in (loc or ""), (code, loc))
lea.req("/admin/index.php?r=account"); lea.post("/admin/index.php?r=account", {"current": "leaderlea1neu", "new": "leaderlea1", "repeat": "leaderlea1"})
for f in ["/src/views/study.php", "/src/views/layout.php", "/tests/run.php", "/tests/i18n.php", "/src/Admin.php", "/config.sample.php"]:
    r = urllib.request.urlopen(BASE + f)
    check("direct request does nothing: " + f, r.status == 200 and r.read() == b"")

print("Delete all answers of a study")
lea.req(f"/admin/index.php?r=study&id={sid}")
code, _, html = lea.post(f"/admin/index.php?r=study.reset&id={sid}", {"confirm": "falsch"})
check("reset needs the link name", "Kürzel der Studie exakt" in html)
code, _, html = lea.post(f"/admin/index.php?r=study.reset&id={sid}", {"confirm": "akku"})
check("all answers deleted, study kept", "Probanden und ihre Antworten gelöscht" in html and "Design-Diagnose" in html)
code, _, csvtext = lea.req(f"/admin/index.php?r=study.export&id={sid}&scope=all&tests=1")
check("export is empty after the reset", len(csvtext.strip().splitlines()) == 1)

print("Unipark ticket parameter")
code, _, html = Client().req("/index.php?s=akku&tic=EFS42")
check("tic is accepted as ID", code == 200 and "alert-error" not in html, code)
con0 = sqlite3.connect(os.path.join(os.path.dirname(LOCK), "e2e.sqlite"))
check("tic stored as the respondent ID", con0.execute("SELECT COUNT(*) FROM tst_respondents WHERE ext_id = 'EFS42'").fetchone()[0] == 1)
con0.close()

print("Database update")
DB = os.path.join(os.path.dirname(LOCK), "e2e.sqlite")
con = sqlite3.connect(DB)
con.execute("UPDATE tst_meta SET value = '1' WHERE name = 'schema_version'"); con.commit()
code, _, html = Client().req("/index.php?s=akku&id=UPD1")
check("respondents see a maintenance notice while an update is pending", code == 503 and ("being updated" in html or "aktualisiert" in html), code)
code, _, _ = lea.req("/admin/index.php?r=overview")
check("project leaders cannot work while an update is pending", code == 503, code)
code, _, html = admin.req("/admin/index.php?r=overview")
check("admin is sent to the update", "Datenbank-Update" in html and "002_variable_names_throttle.sql" in html)
code, _, html = admin.post("/admin/index.php?r=system.migrate", {})
check("update runs from the button", "auf Version 2 aktualisiert" in html, re.findall(r'alert[^>]*>(.*?)<', html))
check("version recorded", con.execute("SELECT value FROM tst_meta WHERE name = 'schema_version'").fetchone()[0] == "2")
code, _, _ = lea.req("/admin/index.php?r=overview")
check("project leaders can work again", code == 200, code)
code, _, html = admin.req("/admin/index.php?r=system")
check("system page shows version and prefix", "Datenbankversion" in html and "tst_" in html and "Update ausstehend" not in html)
con.close()

print("Installer")
code, _, html = Client().req("/install.php?lang=de")
check("installer locked once configured", "Bereits installiert" in html and "Verbindung testen" not in html)
code, _, html = Client().req("/install.php", {"lang": "de", "action": "reset_config", "reset_code": "falsch"})
check("wrong emergency code rejected", "stimmt nicht" in html)
code, _, html = Client().req("/install.php", {"lang": "de", "action": "reset_config", "reset_code": "TEST-CODE-1234"})
check("emergency code deletes config.php", "wurde gelöscht" in html and "Verbindung testen" in html)
code, loc, _ = Client().req("/index.php", follow=False)
check("without config ANOUK sends to the installer", code in (302, 303) and "install.php" in (loc or ""), (code, loc))
code, _, html = Client().req("/install.php", {"lang": "de", "action": "test", "db_host": "127.0.0.1", "db_port": "1", "db_name": "anouk", "db_user": "u", "db_pass": ""})
check("connection test reports failure in plain language", "Verbindungstest fehlgeschlagen" in html and "nicht erreichbar" in html, re.findall(r"<li>(.*?)</li>", html)[:2])
check("form keeps entered values", 'value="anouk"' in html)
code, _, html = Client().req("/install.php", {"lang": "de", "action": "test", "db_host": "127.0.0.1", "db_port": "1", "db_name": "anouk", "db_user": "u", "db_pass": "", "db_prefix": "Bad-Prefix"})
check("invalid table prefix rejected", "Tabellenpräfix darf" in html)
open(LOCK, "w").close()
code, _, html = Client().req("/install.php?lang=de")
check("lock file without config.php keeps the installer locked", "Installer gesperrt" in html and "Verbindung testen" not in html)
os.remove(LOCK)

print(f"\n{'FAILED: ' + str(fails) if fails else 'all passed'}")
sys.exit(1 if fails else 0)
