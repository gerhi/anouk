// ANOUK respondent page: answer check, response time, screen width, no double submit.
(function () {
  var form = document.getElementById('task-form');
  if (!form) return;
  var t0 = performance.now();
  var sent = false;
  form.addEventListener('submit', function (e) {
    if (!form.querySelector('input[name="choice"]:checked')) {
      e.preventDefault();
      var p = document.getElementById('task-error');
      if (p) p.textContent = form.getAttribute('data-choose') || '';
      return;
    }
    if (sent) { e.preventDefault(); return; }
    sent = true;
    document.getElementById('anouk-rt').value = Math.round(performance.now() - t0);
    document.getElementById('anouk-sw').value = window.innerWidth;
    var btn = form.querySelector('button[type="submit"]');
    if (btn) btn.disabled = true;
  });
})();
