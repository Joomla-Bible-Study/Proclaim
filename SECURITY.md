# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 10.x.x  | :white_check_mark: |
| 9.x.x   | :white_check_mark: |
| < 9.0   | :x:                |

## Security Features

### Content Security Policy (CSP) Headers (10.1+)

Proclaim supports Content Security Policy headers to mitigate cross-site scripting (XSS) and data injection attacks on frontend pages.

- **Enable via**: Components > Proclaim > Options > Security tab > "Enable CSP Headers"
- **Default policy**: Allows `self`, inline scripts/styles, and trusted media domains (YouTube, Vimeo)
- **Custom domains**: Add additional trusted domains in the "Extra CSP Sources" setting
- CSP headers are only applied to frontend (site) pages, not the admin panel

### Rate Limiting (10.1+)

Session-based rate limiting protects against rapid form submission abuse on admin controllers.

- **Default**: Maximum 10 POST requests per 60-second window
- **Configurable via**: Components > Proclaim > Options > Security tab
- Applies to all `com_proclaim` POST submissions in the administrator area
- Exceeding the limit displays a warning and blocks the request

### Audit Logging (10.1+)

Sensitive operations are logged via Joomla's built-in Action Logs system (`com_actionlogs`).

Logged actions include:
- Messages: create, update, delete
- Teachers: create, update, delete
- Servers: create, update, delete
- Podcasts: create, update, delete
- Templates: create, update, delete

View audit logs in: Components > Action Logs in the Joomla admin panel.

### SQL Injection Prevention

All database queries use parameterized bindings (`ParameterType`) and `quoteName()` for identifiers. No raw user input is concatenated into SQL strings.

### Form Validation

All entity forms enforce server-side validation via Joomla's form filtering (`filter="int"`, `filter="trim"`, `validate="url"`, etc.) and Table `check()` methods that run before every save.

## Reporting a Vulnerability

If you discover a security vulnerability within this project, please send an email to info@christianwebministries.org. All security vulnerabilities will be promptly addressed.
