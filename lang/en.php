<?php
/**
 * ANOUK interface texts: English.
 *
 * To add a language, copy lang/en.php to lang/<code>.php (two lower-case letters,
 * e.g. fr.php), set '_name' and translate the values. The keys stay as they are.
 * Missing or empty values fall back to English. Placeholders: %s, %d, %1$s …
 */
defined('ANOUK_ROOT') or exit;

return [
    '_name' => 'English',

    // ---- respondent pages (survey.*) -------------------------------------
    'survey.title' => 'Survey',
    'survey.question' => 'Which of these options would you choose?',
    'survey.none' => 'None of these',
    'survey.progress' => 'Choice %d of %d',
    'survey.next' => 'Next',
    'survey.start' => 'Start',
    'survey.choose' => 'Please choose one of the options.',
    'survey.option' => 'Option %s',
    'survey.saved_note' => 'Your answer is saved after each choice.',
    'survey.closed' => 'This survey is currently not active.',
    'survey.notfound' => 'This survey was not found.',
    'survey.badid' => 'The link does not contain a valid participation ID. Please start from the link in your invitation.',
    'survey.thanks' => 'Thank you! This part of the survey is complete. You can close this window now.',
    'survey.thanks_title' => 'Thank you!',
    'survey.preview' => 'Preview – answers are not saved.',
    'survey.preview_end' => 'End of preview. In the field, ANOUK would now redirect to:',
    'survey.no_return' => 'No return URL is set; participants would see a thank-you page.',
    'survey.restart' => 'Restart preview',
    'survey.back' => 'Back to ANOUK',
    'survey.nodesign' => 'This study has no design yet.',
    'survey.cookies' => 'Your browser does not accept the cookie this survey needs to save your answers. Please allow cookies for this site and start again from the link in the survey.',
    'survey.maintenance' => 'This survey is being updated. Please try again in a few minutes.',

    // ---- admin area and installer: key = English text -----------------------
    '%1$d of %2$d'
        => '%1$d of %2$d',
    '%1$d versions × %2$d tasks × %3$d alternatives'
        => '%1$d versions × %2$d tasks × %3$d alternatives',
    '%d attributes and their levels were taken from the file.'
        => '%d attributes and their levels were taken from the file.',
    '%d attributes saved.'
        => '%d attributes saved.',
    '%d respondents'
        => '%d respondents',
    '%d respondents and their answers deleted. The study and its design are kept.'
        => '%d respondents and their answers deleted. The study and its design are kept.',
    '1. Create choice sets with cbcTools'
        => '1. Create choice sets with cbcTools',
    '1. Run this script in R (written for cbcTools 0.7 or newer: install.packages("cbcTools")).'
        => '1. Run this script in R (written for cbcTools 0.7 or newer: install.packages("cbcTools")).',
    '2. Upload the CSV'
        => '2. Upload the CSV',
    '2. Upload the file %s in ANOUK under “Design”.'
        => '2. Upload the file %s in ANOUK under “Design”.',
    '<b>%1$d</b> versions × <b>%2$d</b> tasks × <b>%3$d</b> alternatives'
        => '<b>%1$d</b> versions × <b>%2$d</b> tasks × <b>%3$d</b> alternatives',
    '<b>Codebook</b>: all columns with label, type and values.'
        => '<b>Codebook</b>: all columns with label, type and values.',
    '<b>Long</b>: one row per ID × task × alternative, <code>choice</code> = 1 for the chosen alternative. Ready for <code>logitr</code>, <code>mlogit</code> and <code>bayesm</code>.'
        => '<b>Long</b>: one row per ID × task × alternative, <code>choice</code> = 1 for the chosen alternative. Ready for <code>logitr</code>, <code>mlogit</code> and <code>bayesm</code>.',
    '<b>Wide</b>: one row per ID with the chosen alternative (<code>choice_t01</code> …) and the response time (<code>rt_t01</code> …) per task; to merge with the survey data.'
        => '<b>Wide</b>: one row per ID with the chosen alternative (<code>choice_t01</code> …) and the response time (<code>rt_t01</code> …) per task; to merge with the survey data.',
    '<code>profileID</code>, <code>obsID</code>, <code>blockID</code> and <code>choice</code> are ignored, no-choice rows removed.'
        => '<code>profileID</code>, <code>obsID</code>, <code>blockID</code> and <code>choice</code> are ignored, no-choice rows removed.',
    'A database update is pending. Please ask the administrator to log in and run it.'
        => 'A database update is pending. Please ask the administrator to log in and run it.',
    'A deactivated account can no longer log in; its studies stay as they are.'
        => 'A deactivated account can no longer log in; its studies stay as they are.',
    'A design exists already (%d versions). A new upload replaces it.'
        => 'A design exists already (%d versions). A new upload replaces it.',
    'A design exists already. Delete the design first, then change the attributes.'
        => 'A design exists already. Delete the design first, then change the attributes.',
    'A required component is missing. Ask your provider to enable it (often a PHP version setting in the control panel).'
        => 'A required component is missing. Ask your provider to enable it (often a PHP version setting in the control panel).',
    'Access'
        => 'Access',
    'Account'
        => 'Account',
    'Account activated.'
        => 'Account activated.',
    'Account deactivated.'
        => 'Account deactivated.',
    'Account for %s created.'
        => 'Account for %s created.',
    'Account updated.'
        => 'Account updated.',
    'Account “%1$s” deleted; %2$d studies were transferred.'
        => 'Account “%1$s” deleted; %2$d studies were transferred.',
    'Account “%s” deleted.'
        => 'Account “%s” deleted.',
    'Accounts'
        => 'Accounts',
    'Activate'
        => 'Activate',
    'Active'
        => 'Active',
    'Administration'
        => 'Administration',
    'Administrator'
        => 'Administrator',
    'Administrator account'
        => 'Administrator account',
    'Afterwards ANOUK can be set up again here, for example with new database credentials. Studies and accounts in the database are kept. Administrators can also do this without the code under “System”.'
        => 'Afterwards ANOUK can be set up again here, for example with new database credentials. Studies and accounts in the database are kept. Administrators can also do this without the code under “System”.',
    'All'
        => 'All',
    'All ANOUK tables start with this prefix, so ANOUK can share a database with other tools. Keep <code>anouk_</code> unless you run two ANOUK installations in one database. To reuse an existing ANOUK database, enter its prefix.'
        => 'All ANOUK tables start with this prefix, so ANOUK can share a database with other tools. Keep <code>anouk_</code> unless you run two ANOUK installations in one database. To reuse an existing ANOUK database, enter its prefix.',
    'All studies'
        => 'All studies',
    'all, including drop-outs'
        => 'all, including drop-outs',
    'Already installed'
        => 'Already installed',
    'alternative'
        => 'alternative',
    'alternatives per choice set'
        => 'alternatives per choice set',
    'Alternatives per set'
        => 'Alternatives per set',
    'Alternatives per task'
        => 'Alternatives per task',
    'Alternatives per task: 2 to 10.'
        => 'Alternatives per task: 2 to 10.',
    'Always use HTTPS (redirect http:// to https://)'
        => 'Always use HTTPS (redirect http:// to https://)',
    'ANOUK cannot reach the database configured in config.php. Check the access data. To set ANOUK up again, delete config.php by FTP or reset it in install.php with the emergency code.'
        => 'ANOUK cannot reach the database configured in config.php. Check the access data. To set ANOUK up again, delete config.php by FTP or reset it in install.php with the emergency code.',
    'ANOUK is installed'
        => 'ANOUK is installed',
    'ANOUK is installed (<code>config.php</code> exists).'
        => 'ANOUK is installed (<code>config.php</code> exists).',
    'ANOUK uses the existing database with %d accounts.'
        => 'ANOUK uses the existing database with %d accounts.',
    'ANOUK writes an R script that builds the choice sets with <code>cbcTools</code> and saves them as CSV. Run it in R, then upload the CSV.'
        => 'ANOUK writes an R script that builds the choice sets with <code>cbcTools</code> and saves them as CSV. Run it in R, then upload the CSV.',
    'ANOUK writes an R script with the attributes of this study. <a href="%s">Define the attributes</a> first.'
        => 'ANOUK writes an R script with the attributes of this study. <a href="%s">Define the attributes</a> first.',
    'ANOUK – create choice sets with cbcTools'
        => 'ANOUK – create choice sets with cbcTools',
    'ANOUK: installation'
        => 'ANOUK: installation',
    'Answers'
        => 'Answers',
    'Assign columns yourself'
        => 'Assign columns yourself',
    'At least 10 characters.'
        => 'At least 10 characters.',
    'Attribute'
        => 'Attribute',
    'Attributes and design can only be changed while the study is a draft.'
        => 'Attributes and design can only be changed while the study is a draft.',
    'Attributes and levels'
        => 'Attributes and levels',
    'automatic'
        => 'automatic',
    'Back to draft'
        => 'Back to draft',
    'Back to the overview'
        => 'Back to the overview',
    'Back up the database first if you can (for example with phpMyAdmin of your hosting provider). The update keeps all studies, accounts and answers.'
        => 'Back up the database first if you can (for example with phpMyAdmin of your hosting provider). The update keeps all studies, accounts and answers.',
    'balanced – equal level frequencies'
        => 'balanced – equal level frequencies',
    'Cancel'
        => 'Cancel',
    'Change password'
        => 'Change password',
    'Check'
        => 'Check',
    'choice sets per version'
        => 'choice sets per version',
    'Choice-based conjoint studies with LimeSurvey'
        => 'Choice-based conjoint studies with LimeSurvey',
    'Choose a CSV file.'
        => 'Choose a CSV file.',
    'Choose the account that takes over the studies.'
        => 'Choose the account that takes over the studies.',
    'chose %s'
        => 'chose %s',
    'Chosen by the person'
        => 'Chosen by the person',
    'Close'
        => 'Close',
    'Closed'
        => 'Closed',
    'Column for the alternative'
        => 'Column for the alternative',
    'Column for the task'
        => 'Column for the task',
    'Column for the version'
        => 'Column for the version',
    'Column names and levels must match the defined attributes.'
        => 'Column names and levels must match the defined attributes.',
    'Column names of the attributes in exports and in the cbcTools script, for example <code>price</code> instead of “Price per month”. Lower-case letters, digits and underscores, starting with a letter. You can change them at any time, also while the study is in the field.'
        => 'Column names of the attributes in exports and in the cbcTools script, for example <code>price</code> instead of “Price per month”. Lower-case letters, digits and underscores, starting with a letter. You can change them at any time, also while the study is in the field.',
    'Completed'
        => 'Completed',
    'completed'
        => 'completed',
    'Completed in the last 7 days'
        => 'Completed in the last 7 days',
    'completed only'
        => 'completed only',
    'Completed respondents'
        => 'Completed respondents',
    'Completions per used version'
        => 'Completions per used version',
    'config.php could not be deleted. Delete it by FTP.'
        => 'config.php could not be deleted. Delete it by FTP.',
    'config.php has been deleted. ANOUK can now be set up again; the data in the database are kept.'
        => 'config.php has been deleted. ANOUK can now be set up again; the data in the database are kept.',
    'Connected, server version %s.'
        => 'Connected, server version %s.',
    'Connection test failed'
        => 'Connection test failed',
    'Connection test passed'
        => 'Connection test passed',
    'Contents'
        => 'Contents',
    'Copied'
        => 'Copied',
    'Copy'
        => 'Copy',
    'Copy link'
        => 'Copy link',
    'Create a design first'
        => 'Create a design first',
    'Create a new starting password?'
        => 'Create a new starting password?',
    'Create a study under <a href="%s">New study</a> and define its attributes and levels.'
        => 'Create a study under <a href="%s">New study</a> and define its attributes and levels.',
    'Create account'
        => 'Create account',
    'Create or upload'
        => 'Create or upload',
    'Create study'
        => 'Create study',
    'Current password'
        => 'Current password',
    'Data exist already; the design cannot be deleted.'
        => 'Data exist already; the design cannot be deleted.',
    'Database (MySQL or MariaDB)'
        => 'Database (MySQL or MariaDB)',
    'Database error: %s'
        => 'Database error: %s',
    'Database name'
        => 'Database name',
    'Database not reachable'
        => 'Database not reachable',
    'Database password'
        => 'Database password',
    'Database server'
        => 'Database server',
    'Database update'
        => 'Database update',
    'Database updated to version %d (%s).'
        => 'Database updated to version %d (%s).',
    'Database user'
        => 'Database user',
    'Database version'
        => 'Database version',
    'Deactivate'
        => 'Deactivate',
    'Define'
        => 'Define',
    'Define attributes and levels before downloading the R script.'
        => 'Define attributes and levels before downloading the R script.',
    'Define attributes and levels first.'
        => 'Define attributes and levels first.',
    'Delete account'
        => 'Delete account',
    'Delete all answers'
        => 'Delete all answers',
    'Delete config.php'
        => 'Delete config.php',
    'Delete design'
        => 'Delete design',
    'Delete for good'
        => 'Delete for good',
    'Delete install.php'
        => 'Delete install.php',
    'Delete install.php and log in'
        => 'Delete install.php and log in',
    'Delete respondent'
        => 'Delete respondent',
    'Delete study'
        => 'Delete study',
    'Delete this account for good'
        => 'Delete this account for good',
    'deleted'
        => 'deleted',
    'Deletes <code>config.php</code> and the lock file <code>data/install.lock</code>. ANOUK then leads to <code>install.php</code>, where the database connection can be set up again. Studies, accounts and answers in the database are kept.'
        => 'Deletes <code>config.php</code> and the lock file <code>data/install.lock</code>. ANOUK then leads to <code>install.php</code>, where the database connection can be set up again. Studies, accounts and answers in the database are kept.',
    'Deletes all respondents and answers of this study, for example after a test run. The study, its settings and its design are kept.'
        => 'Deletes all respondents and answers of this study, for example after a test run. The study, its settings and its design are kept.',
    'Deletes the study with its design and all answers for good.'
        => 'Deletes the study with its design and all answers for good.',
    'Deletes this respondent and all their answers, for example on a deletion request. Their version becomes free again.'
        => 'Deletes this respondent and all their answers, for example on a deletion request. Their version becomes free again.',
    'Design'
        => 'Design',
    'Design deleted.'
        => 'Design deleted.',
    'Design diagnostics: level frequencies and overlap'
        => 'Design diagnostics: level frequencies and overlap',
    'Design generated: %1$d versions × %2$d tasks × %3$d alternatives (seed %4$d).'
        => 'Design generated: %1$d versions × %2$d tasks × %3$d alternatives (seed %4$d).',
    'Design saved: %1$d versions × %2$d tasks × %3$d alternatives.'
        => 'Design saved: %1$d versions × %2$d tasks × %3$d alternatives.',
    'Details'
        => 'Details',
    'Do not add a "no choice" option here; switch on the none option in ANOUK instead.'
        => 'Do not add a "no choice" option here; switch on the none option in ANOUK instead.',
    'Download as CSV'
        => 'Download as CSV',
    'Download CSV'
        => 'Download CSV',
    'Download R script'
        => 'Download R script',
    'Draft'
        => 'Draft',
    'Dropped out'
        => 'Dropped out',
    'E-mail address'
        => 'E-mail address',
    'E-mail address or password is wrong.'
        => 'E-mail address or password is wrong.',
    'e.g. battery-study'
        => 'e.g. battery-study',
    'Edit'
        => 'Edit',
    'Emergency code'
        => 'Emergency code',
    'Emergency code from the installation'
        => 'Emergency code from the installation',
    'Enter a name and a valid e-mail address for the administrator.'
        => 'Enter a name and a valid e-mail address for the administrator.',
    'Enter a name and a valid e-mail address.'
        => 'Enter a name and a valid e-mail address.',
    'Enter a name.'
        => 'Enter a name.',
    'Enter a title (at most 255 characters).'
        => 'Enter a title (at most 255 characters).',
    'Enter a valid e-mail address.'
        => 'Enter a valid e-mail address.',
    'Enter at least one attribute.'
        => 'Enter at least one attribute.',
    'Enter the database name and user.'
        => 'Enter the database name and user.',
    'Enter the return URL to LimeSurvey in the settings, check the preview and send the study into the field.'
        => 'Enter the return URL to LimeSurvey in the settings, check the preview and send the study into the field.',
    'Entry link for LimeSurvey'
        => 'Entry link for LimeSurvey',
    'Error %d'
        => 'Error %d',
    'error URL'
        => 'error URL',
    'Error URL (optional)'
        => 'Error URL (optional)',
    'Every task needs at least two alternatives.'
        => 'Every task needs at least two alternatives.',
    'Existing ANOUK database found (%d accounts). It will be used again; no new administrator is needed.'
        => 'Existing ANOUK database found (%d accounts). It will be used again; no new administrator is needed.',
    'Export'
        => 'Export',
    'Field status'
        => 'Field status',
    'File (CSV with comma, semicolon or tab)'
        => 'File (CSV with comma, semicolon or tab)',
    'File style'
        => 'File style',
    'Fill in the database details from your hosting provider. Use <em>Test connection</em> first; nothing is saved until you click <em>Install</em>.'
        => 'Fill in the database details from your hosting provider. Use <em>Test connection</em> first; nothing is saved until you click <em>Install</em>.',
    'Filter'
        => 'Filter',
    'Fixed texts such as buttons and messages follow this language.'
        => 'Fixed texts such as buttons and messages follow this language.',
    'Folder data/ writable (for the lock file)'
        => 'Folder data/ writable (for the lock file)',
    'Folder writable (for config.php)'
        => 'Folder writable (for config.php)',
    'For D-efficient designs with priors see ?cbc_priors and method = "cea" or "modfed".'
        => 'For D-efficient designs with priors see ?cbc_priors and method = "cea" or "modfed".',
    'For security, delete <code>install.php</code> now.'
        => 'For security, delete <code>install.php</code> now.',
    'For tests, or when R is not at hand. For the actual study, cbcTools is preferable.'
        => 'For tests, or when R is not at hand. For the actual study, cbcTools is preferable.',
    'Format'
        => 'Format',
    'Further errors are not shown.'
        => 'Further errors are not shown.',
    'Generate in ANOUK'
        => 'Generate in ANOUK',
    'generated with “%1$s”, seed %2$s'
        => 'generated with “%1$s”, seed %2$s',
    'German Excel (semicolon)'
        => 'German Excel (semicolon)',
    'Getting started'
        => 'Getting started',
    'Help'
        => 'Help',
    'Host'
        => 'Host',
    'Host, port or database name contain invalid characters.'
        => 'Host, port or database name contain invalid characters.',
    'HTTPS'
        => 'HTTPS',
    'ID'
        => 'ID',
    'IDs that start with <code>%s</code> are test IDs.'
        => 'IDs that start with <code>%s</code> are test IDs.',
    'If this database comes from an older ANOUK version, the administrator is offered a database update after logging in.'
        => 'If this database comes from an older ANOUK version, the administrator is offered a database update after logging in.',
    'Ignore columns (comma-separated)'
        => 'Ignore columns (comma-separated)',
    'In progress'
        => 'In progress',
    'In the field'
        => 'In the field',
    'Inactive'
        => 'Inactive',
    'Include test IDs'
        => 'Include test IDs',
    'Install'
        => 'Install',
    'install.php could not be deleted. Delete it by FTP.'
        => 'install.php could not be deleted. Delete it by FTP.',
    'install.php has been deleted.'
        => 'install.php has been deleted.',
    'install.php is missing. Upload install.php again first, otherwise ANOUK cannot be set up afterwards.'
        => 'install.php is missing. Upload install.php again first, otherwise ANOUK cannot be set up afterwards.',
    'Installation'
        => 'Installation',
    'Installer locked'
        => 'Installer locked',
    'Introduction before the first task (optional)'
        => 'Introduction before the first task (optional)',
    'It is shown only now. Pass it on safely; the person has to choose a password of their own at the first login.'
        => 'It is shown only now. Pass it on safely; the person has to choose a password of their own at the first login.',
    'Label of the none option'
        => 'Label of the none option',
    'Language'
        => 'Language',
    'Language of the respondent pages'
        => 'Language of the respondent pages',
    'Last activity'
        => 'Last activity',
    'Last login'
        => 'Last login',
    'Leave empty for the default text of the chosen language.'
        => 'Leave empty for the default text of the chosen language.',
    'level labels instead of dummy codes'
        => 'level labels instead of dummy codes',
    'Levels'
        => 'Levels',
    'LimeSurvey sends respondents to this link; it replaces {TOKEN} with the participant’s token.'
        => 'LimeSurvey sends respondents to this link; it replaces {TOKEN} with the participant’s token.',
    'Line %1$d: the attribute “%2$s” exists already.'
        => 'Line %1$d: the attribute “%2$s” exists already.',
    'Line %1$d: the attribute “%3$s” has no level “%2$s”.'
        => 'Line %1$d: the attribute “%3$s” has no level “%2$s”.',
    'Line %1$d: the value for the attribute “%2$s” is missing.'
        => 'Line %1$d: the value for the attribute “%2$s” is missing.',
    'Line %1$d: version %2$d, task %3$d, alternative %4$d occurs twice.'
        => 'Line %1$d: version %2$d, task %3$d, alternative %4$d occurs twice.',
    'Line %1$d: “%2$s” contains a level twice.'
        => 'Line %1$d: “%2$s” contains a level twice.',
    'Line %1$d: “%2$s” is not a valid number for the %3$s.'
        => 'Line %1$d: “%2$s” is not a valid number for the %3$s.',
    'Line %1$d: “%2$s” needs at least two levels.'
        => 'Line %1$d: “%2$s” needs at least two levels.',
    'Line %d: a level is too long (at most 255 characters).'
        => 'Line %d: a level is too long (at most 255 characters).',
    'Line %d: the attribute name is missing.'
        => 'Line %d: the attribute name is missing.',
    'Line %d: the attribute name is too long (at most 190 characters).'
        => 'Line %d: the attribute name is too long (at most 190 characters).',
    'Line %d: the colon is missing. Format: Name: Level 1 | Level 2'
        => 'Line %d: the colon is missing. Format: Name: Level 1 | Level 2',
    'Link LimeSurvey to the entry link of the study and download the data in long format afterwards.'
        => 'Link LimeSurvey to the entry link of the study and download the data in long format afterwards.',
    'Link name'
        => 'Link name',
    'Link with LimeSurvey'
        => 'Link with LimeSurvey',
    'Log in'
        => 'Log in',
    'Log in without deleting'
        => 'Log in without deleting',
    'Log out'
        => 'Log out',
    'Lower-case letters, digits and hyphens. It appears in the link for respondents and cannot be changed later.'
        => 'Lower-case letters, digits and hyphens. It appears in the link for respondents and cannot be changed later.',
    'Manage accounts'
        => 'Manage accounts',
    'Method'
        => 'Method',
    'Minimal overlap (recommended)'
        => 'Minimal overlap (recommended)',
    'minoverlap – little overlap (recommended)'
        => 'minoverlap – little overlap (recommended)',
    'Name'
        => 'Name',
    'Never'
        => 'Never',
    'New account'
        => 'New account',
    'New password'
        => 'New password',
    'New starting password'
        => 'New starting password',
    'New starting password created. It has to be changed at the next login.'
        => 'New starting password created. It has to be changed at the next login.',
    'New study'
        => 'New study',
    'Next'
        => 'Next',
    'No access'
        => 'No access',
    'No attributes yet. Define them here, or take them from the file when you upload a design.'
        => 'No attributes yet. Define them here, or take them from the file when you upload a design.',
    'No column found for “%1$s”. Expected for example %2$s, or enter the column name in the form.'
        => 'No column found for “%1$s”. Expected for example %2$s, or enter the column name in the form.',
    'No connection: %s'
        => 'No connection: %s',
    'No design yet.'
        => 'No design yet.',
    'No new choice task found for version %1$d, task %2$d. Choose fewer tasks or alternatives.'
        => 'No new choice task found for version %1$d, task %2$d. Choose fewer tasks or alternatives.',
    'No respondents found.'
        => 'No respondents found.',
    'No studies yet. Create a study to get a link for LimeSurvey.'
        => 'No studies yet. Create a study to get a link for LimeSurvey.',
    'none chosen'
        => 'none chosen',
    'None option'
        => 'None option',
    'None yet'
        => 'None yet',
    'none yet'
        => 'none yet',
    'Not found'
        => 'Not found',
    'not set – respondents only see a thank-you page.'
        => 'not set – respondents only see a thank-you page.',
    'Number of versions: 1 to 10,000.'
        => 'Number of versions: 1 to 10,000.',
    'Offer a none option'
        => 'Offer a none option',
    'Often <code>localhost</code>; some providers use a separate name.'
        => 'Often <code>localhost</code>; some providers use a separate name.',
    'One attribute per line, levels separated by <code>|</code>'
        => 'One attribute per line, levels separated by <code>|</code>',
    'Or upload a cbcTools design of your own right away; the attributes are then taken from the file.'
        => 'Or upload a cbcTools design of your own right away; the attributes are then taken from the file.',
    'otherwise the design no longer matches the attributes of the study.'
        => 'otherwise the design no longer matches the attributes of the study.',
    'Output of <code>cbcTools</code> (columns <code>respID</code>, <code>qID</code>, <code>altID</code> and one column per attribute) or any other design in long format:'
        => 'Output of <code>cbcTools</code> (columns <code>respID</code>, <code>qID</code>, <code>altID</code> and one column per attribute) or any other design in long format:',
    'Overview'
        => 'Overview',
    'Owner'
        => 'Owner',
    'Page %1$d of %2$d'
        => 'Page %1$d of %2$d',
    'Password'
        => 'Password',
    'Password changed.'
        => 'Password changed.',
    'Pending updates:'
        => 'Pending updates:',
    'PHP 8.1 or newer (found: %s)'
        => 'PHP 8.1 or newer (found: %s)',
    'PHP extension mbstring'
        => 'PHP extension mbstring',
    'PHP extension pdo_mysql'
        => 'PHP extension pdo_mysql',
    'Placeholders: <code>{ID}</code> = the respondent’s ID, <code>{STATUS}</code> = <code>complete</code>. To return into the same survey, LimeSurvey needs a participant list with token-based response persistence.'
        => 'Placeholders: <code>{ID}</code> = the respondent’s ID, <code>{STATUS}</code> = <code>complete</code>. To return into the same survey, LimeSurvey needs a participant list with token-based response persistence.',
    'Plain text; blank lines separate paragraphs. Leave empty to start with the first task.'
        => 'Plain text; blank lines separate paragraphs. Leave empty to start with the first task.',
    'Port'
        => 'Port',
    'Prefix of test IDs'
        => 'Prefix of test IDs',
    'present'
        => 'present',
    'Preview'
        => 'Preview',
    'Previous'
        => 'Previous',
    'Project leader'
        => 'Project leader',
    'Project leaders see and edit only their own studies. ANOUK creates a starting password, which has to be changed at the first login.'
        => 'Project leaders see and edit only their own studies. ANOUK creates a starting password, which has to be changed at the first login.',
    'Question above each task'
        => 'Question above each task',
    'R, SPSS, Python (comma)'
        => 'R, SPSS, Python (comma)',
    'Random'
        => 'Random',
    'Random attribute order per respondent (the same for all of their tasks)'
        => 'Random attribute order per respondent (the same for all of their tasks)',
    'random – random profiles'
        => 'random – random profiles',
    'Really delete the design?'
        => 'Really delete the design?',
    'Really delete this respondent and all their answers?'
        => 'Really delete this respondent and all their answers?',
    'Release the version of a drop-out after (minutes)'
        => 'Release the version of a drop-out after (minutes)',
    'Repeat new password'
        => 'Repeat new password',
    'Repeat password'
        => 'Repeat password',
    'Replace'
        => 'Replace',
    'Requirements'
        => 'Requirements',
    'Reset the configuration'
        => 'Reset the configuration',
    'Reset the configuration (delete config.php)'
        => 'Reset the configuration (delete config.php)',
    'Respondent'
        => 'Respondent',
    'Respondent pages'
        => 'Respondent pages',
    'Respondent “%s” and their answers deleted.'
        => 'Respondent “%s” and their answers deleted.',
    'Respondents'
        => 'Respondents',
    'Return after completion:'
        => 'Return after completion:',
    'Return to LimeSurvey'
        => 'Return to LimeSurvey',
    'return URL'
        => 'return URL',
    'Return URL after completion'
        => 'Return URL after completion',
    'Returns to the survey'
        => 'Returns to the survey',
    'Role'
        => 'Role',
    'Save'
        => 'Save',
    'Save for ANOUK'
        => 'Save for ANOUK',
    'Save variable names'
        => 'Save variable names',
    'Saved: '
        => 'Saved: ',
    'Search ID'
        => 'Search ID',
    'Seed (empty = random)'
        => 'Seed (empty = random)',
    'Send into the field'
        => 'Send into the field',
    'Set it'
        => 'Set it',
    'Sets per version'
        => 'Sets per version',
    'Settings'
        => 'Settings',
    'Settings saved.'
        => 'Settings saved.',
    'Share'
        => 'Share',
    'shortcut – a compromise'
        => 'shortcut – a compromise',
    'Show progress'
        => 'Show progress',
    'Show respondents'
        => 'Show respondents',
    'Started'
        => 'Started',
    'Started, not completed'
        => 'Started, not completed',
    'Starting password'
        => 'Starting password',
    'Starting password for <b>%s</b>:'
        => 'Starting password for <b>%s</b>:',
    'Starting password, not yet changed'
        => 'Starting password, not yet changed',
    'Status'
        => 'Status',
    'Status: %s.'
        => 'Status: %s.',
    'Studies'
        => 'Studies',
    'Study'
        => 'Study',
    'Study created. Next, define the attributes or upload a design straight away.'
        => 'Study created. Next, define the attributes or upload a design straight away.',
    'Study “%s” and all its data deleted.'
        => 'Study “%s” and all its data deleted.',
    'Study: %1$s (%2$s), script created on %3$s'
        => 'Study: %1$s (%2$s), script created on %3$s',
    'System'
        => 'System',
    'Table prefix'
        => 'Table prefix',
    'Tables and administrator account have been created.'
        => 'Tables and administrator account have been created.',
    'task'
        => 'task',
    'Task %d'
        => 'Task %d',
    'Tasks per version'
        => 'Tasks per version',
    'Tasks per version: 1 to 50.'
        => 'Tasks per version: 1 to 50.',
    'Tasks with a repeated level'
        => 'Tasks with a repeated level',
    'Test'
        => 'Test',
    'Test connection'
        => 'Test connection',
    'Test IDs'
        => 'Test IDs',
    'Test IDs do not count for the version assignment and are left out of the export unless chosen.'
        => 'Test IDs do not count for the version assignment and are left out of the export unless chosen.',
    'The %s must start with http:// or https://.'
        => 'The %s must start with http:// or https://.',
    'The administrator can create further accounts for project leaders after logging in.'
        => 'The administrator can create further accounts for project leaders after logging in.',
    'The administrator password needs at least 10 characters.'
        => 'The administrator password needs at least 10 characters.',
    'The attributes are locked because a design exists or the study is not a draft.'
        => 'The attributes are locked because a design exists or the study is not a draft.',
    'The attributes are taken from the file.'
        => 'The attributes are taken from the file.',
    'The column “%s” matches no defined attribute. If it is not an attribute, enter it under “Ignore columns”.'
        => 'The column “%s” matches no defined attribute. If it is not an attribute, enter it under “Ignore columns”.',
    'The current password is wrong.'
        => 'The current password is wrong.',
    'The database contains %1$d other tables; ANOUK uses its own tables with the prefix %2$s.'
        => 'The database contains %1$d other tables; ANOUK uses its own tables with the prefix %2$s.',
    'The database is empty.'
        => 'The database is empty.',
    'The database server was not reached. Check host and port; sometimes the host is not “localhost”.'
        => 'The database server was not reached. Check host and port; sometimes the host is not “localhost”.',
    'The database update failed: %s'
        => 'The database update failed: %s',
    'The design can only be changed while the study is a draft.'
        => 'The design can only be changed while the study is a draft.',
    'The design was not saved.'
        => 'The design was not saved.',
    'The emergency code is wrong.'
        => 'The emergency code is wrong.',
    'The file contains no attribute columns.'
        => 'The file contains no attribute columns.',
    'The file has no column for the attribute “%s”.'
        => 'The file has no column for the attribute “%s”.',
    'The file has no column “%s”.'
        => 'The file has no column “%s”.',
    'The file is empty or contains only a header row.'
        => 'The file is empty or contains only a header row.',
    'The file is larger than 20 MB.'
        => 'The file is larger than 20 MB.',
    'The files on the server are newer than the database (database version %1$d, needed: %2$d). Until the update has run, respondents see a maintenance notice and project leaders cannot work.'
        => 'The files on the server are newer than the database (database version %1$d, needed: %2$d). Until the update has run, respondents see a maintenance notice and project leaders cannot work.',
    'The folder is not writable. Create a file <code>config.php</code> next to <code>index.php</code> with this content:'
        => 'The folder is not writable. Create a file <code>config.php</code> next to <code>index.php</code> with this content:',
    'The form has expired. Go back, reload the page and try again.'
        => 'The form has expired. Go back, reload the page and try again.',
    'The help file was not found.'
        => 'The help file was not found.',
    'The installer <code>install.php</code> is still on the server. Without the emergency code it cannot be misused, but it should be deleted.'
        => 'The installer <code>install.php</code> is still on the server. Without the emergency code it cannot be misused, but it should be deleted.',
    'The installer is locked by <code>data/install.lock</code>, but <code>config.php</code> is missing. To install again, delete <code>data/install.lock</code> by FTP and reload this page. The data in the database are kept.'
        => 'The installer is locked by <code>data/install.lock</code>, but <code>config.php</code> is missing. To install again, delete <code>data/install.lock</code> by FTP and reload this page. The data in the database are kept.',
    'The last active administrator cannot be deactivated.'
        => 'The last active administrator cannot be deactivated.',
    'The last active administrator cannot be deleted.'
        => 'The last active administrator cannot be deleted.',
    'The last active administrator must keep this role.'
        => 'The last active administrator must keep this role.',
    'The level labels come from ANOUK and must not be changed,'
        => 'The level labels come from ANOUK and must not be changed,',
    'The levels must match an uploaded design exactly. The order of the lines is the display order.'
        => 'The levels must match an uploaded design exactly. The order of the lines is the display order.',
    'The link name may contain only lower-case letters, digits and hyphens (2 to 63 characters).'
        => 'The link name may contain only lower-case letters, digits and hyphens (2 to 63 characters).',
    'The lock file <code>data/install.lock</code> could not be written. Delete <code>install.php</code> now, or make the folder <code>data/</code> writable.'
        => 'The lock file <code>data/install.lock</code> could not be written. Delete <code>install.php</code> now, or make the folder <code>data/</code> writable.',
    'The new password needs at least 10 characters.'
        => 'The new password needs at least 10 characters.',
    'The no-choice rows from cbcTools were removed. For a “none of these” option, switch on the none option in the settings.'
        => 'The no-choice rows from cbcTools were removed. For a “none of these” option, switch on the none option in the settings.',
    'The none option can no longer be changed because data exist.'
        => 'The none option can no longer be changed because data exist.',
    'The release time must be between 5 minutes and 7 days.'
        => 'The release time must be between 5 minutes and 7 days.',
    'The study accepts respondents. Attributes and design are frozen.'
        => 'The study accepts respondents. Attributes and design are frozen.',
    'The study no longer accepts respondents.'
        => 'The study no longer accepts respondents.',
    'The table prefix may contain lower-case letters, digits and underscores, starting with a letter (at most 20 characters).'
        => 'The table prefix may contain lower-case letters, digits and underscores, starting with a letter (at most 20 characters).',
    'The title must not be empty.'
        => 'The title must not be empty.',
    'The two new passwords do not match.'
        => 'The two new passwords do not match.',
    'The two passwords do not match.'
        => 'The two passwords do not match.',
    'The user may create tables.'
        => 'The user may create tables.',
    'The user may not create tables (the CREATE privilege is missing).'
        => 'The user may not create tables (the CREATE privilege is missing).',
    'The variable name “%s” is used twice.'
        => 'The variable name “%s” is used twice.',
    'The variable names below can still be changed.'
        => 'The variable names below can still be changed.',
    'These attributes give only %1$d different profiles, too few for %2$d alternatives.'
        => 'These attributes give only %1$d different profiles, too few for %2$d alternatives.',
    'This account owns %d studies. Choose who takes them over; results stay with the studies.'
        => 'This account owns %d studies. Choose who takes them over; results stay with the studies.',
    'This account owns no studies.'
        => 'This account owns no studies.',
    'This account was not found.'
        => 'This account was not found.',
    'This database does not exist. Create it in your hosting control panel first.'
        => 'This database does not exist. Create it in your hosting control panel first.',
    'This e-mail address is already registered.'
        => 'This e-mail address is already registered.',
    'This link name is already taken.'
        => 'This link name is already taken.',
    'This needs <code>install.php</code> on the server. Upload the file from the ANOUK package again.'
        => 'This needs <code>install.php</code> on the server. Upload the file from the ANOUK package again.',
    'This page does not exist.'
        => 'This page does not exist.',
    'This page is for administrators only.'
        => 'This page is for administrators only.',
    'This respondent was not found.'
        => 'This respondent was not found.',
    'This status change is not possible.'
        => 'This status change is not possible.',
    'This study was not found.'
        => 'This study was not found.',
    'This user has no rights on this database.'
        => 'This user has no rights on this database.',
    'Tick the confirmation box to delete the account.'
        => 'Tick the confirmation box to delete the account.',
    'Title'
        => 'Title',
    'To change the attributes, delete the design first.'
        => 'To change the attributes, delete the design first.',
    'To confirm, type DELETE.'
        => 'To confirm, type DELETE.',
    'To delete, type the link name of the study exactly.'
        => 'To delete, type the link name of the study exactly.',
    'Too many failed attempts. Please try again in %d minutes.'
        => 'Too many failed attempts. Please try again in %d minutes.',
    'Transfer the studies to'
        => 'Transfer the studies to',
    'Type <code>DELETE</code> to confirm'
        => 'Type <code>DELETE</code> to confirm',
    'Type the link name to confirm'
        => 'Type the link name to confirm',
    'Under “Design”, download the R script, create the choice sets with cbcTools and upload the CSV file.'
        => 'Under “Design”, download the R script, create the choice sets with cbcTools and upload the CSV file.',
    'Unknown method.'
        => 'Unknown method.',
    'Update database'
        => 'Update database',
    'update pending'
        => 'update pending',
    'Upload and check'
        => 'Upload and check',
    'uploaded'
        => 'uploaded',
    'uploaded from <code>%s</code>'
        => 'uploaded from <code>%s</code>',
    'Used when the study is not in the field (<code>{STATUS}</code> = <code>closed</code>) or the ID is invalid (<code>invalid_id</code>). Empty: a message page.'
        => 'Used when the study is not in the field (<code>{STATUS}</code> = <code>closed</code>) or the ID is invalid (<code>invalid_id</code>). Empty: a message page.',
    'User name or password is wrong.'
        => 'User name or password is wrong.',
    'Variable name'
        => 'Variable name',
    'Variable names'
        => 'Variable names',
    'Variable names saved.'
        => 'Variable names saved.',
    'Version'
        => 'Version',
    'version'
        => 'version',
    'Version %1$d has %2$d tasks; expected are %3$d (as in the first version).'
        => 'Version %1$d has %2$d tasks; expected are %3$d (as in the first version).',
    'Version %1$d, task %2$d has %3$d alternatives; expected are %4$d.'
        => 'Version %1$d, task %2$d has %3$d alternatives; expected are %4$d.',
    'Version %1$d, task %2$d: alternative %3$d is identical to alternative %4$d.'
        => 'Version %1$d, task %2$d: alternative %3$d is identical to alternative %4$d.',
    'Version %1$d: task %2$d repeats task %3$d.'
        => 'Version %1$d: task %2$d repeats task %3$d.',
    'Versions'
        => 'Versions',
    'Versions and tests'
        => 'Versions and tests',
    'Versions are different questionnaires. ANOUK spreads them evenly; with more respondents than versions, each version is used several times.'
        => 'Versions are different questionnaires. ANOUK spreads them evenly; with more respondents than versions, each version is used several times.',
    'Versions with completions'
        => 'Versions with completions',
    'Versions, tasks or alternatives were renumbered consecutively from 1.'
        => 'Versions, tasks or alternatives were renumbered consecutively from 1.',
    'versions; ANOUK spreads them evenly over the respondents'
        => 'versions; ANOUK spreads them evenly over the respondents',
    'While the study is a draft, attributes and design can be changed. Respondents see nothing yet.'
        => 'While the study is a draft, attributes and design can be changed. Respondents see nothing yet.',
    'With this code, <code>config.php</code> can later be deleted on this page, even when the database can no longer be reached. <b>Write it down now</b>; it is not shown again.'
        => 'With this code, <code>config.php</code> can later be deleted on this page, even when the database can no longer be reached. <b>Write it down now</b>; it is not shown again.',
    'Without a design the study cannot go into the field.'
        => 'Without a design the study cannot go into the field.',
    'without prior assumptions: "random", "shortcut", "minoverlap", "balanced"'
        => 'without prior assumptions: "random", "shortcut", "minoverlap", "balanced"',
    'Without R: generate a simple design in ANOUK'
        => 'Without R: generate a simple design in ANOUK',
    'You are using a starting password. Choose a password of your own to continue.'
        => 'You are using a starting password. Choose a password of your own to continue.',
    'You cannot deactivate your own account.'
        => 'You cannot deactivate your own account.',
    'You cannot delete your own account.'
        => 'You cannot delete your own account.',
    'You cannot take away your own administrator role.'
        => 'You cannot take away your own administrator role.',
    '“%1$s”: the variable name “%2$s” is not valid. Use lower-case letters, digits and underscores, starting with a letter (at most 64 characters); export column names such as id, task or choice are taken.'
        => '“%1$s”: the variable name “%2$s” is not valid. Use lower-case letters, digits and underscores, starting with a letter (at most 64 characters); export column names such as id, task or choice are taken.',
    '“Dropped out” means no activity within the release time of the study; the version is then free for others.'
        => '“Dropped out” means no activity within the release time of the study; the version is then free for others.',
];
