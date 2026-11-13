# eLMS system tests (developer use only)

These scripts drive real browsers (Chromium via Playwright) against a **copy** of the
database. They create test accounts, classes and submissions, so never run them on the
school's live data.

Requirements: Node.js 18+, `npm i playwright`, `npx playwright install chromium`,
`php` and `mysql` on the PATH, the site running (e.g. XAMPP), database `elmsdb`.

| Script | What it checks |
|---|---|
| `e2e.js` | Admin + Teacher + Student in three browsers: accounts, login form, class assignment, enrollment, assignment → submission → grade → attendance → announcement, live updates, section isolation, security probes, offline recovery, deactivation (51 checks) |
| `crud.js` | Every create / edit / delete action of each role, checked against the database (80 checks) |
| `buttons.js` | Every button, link and handler on every page: handler exists, links answer |

Run from this folder: `ELMS_URL=http://localhost/Capstone1 node e2e.js`

`mksess.php` writes a PHP session file so the tests can act as an existing teacher or
student without the e-mailed sign-in code. It only works on the same machine as PHP's
session folder and is not part of the website. Delete the `tests` folder on a live server.
