### Inside this folder contains the working files needed for the PHP-based comment system I built for my website.  
[www.jondstone.com/urbanexplorations](https://www.jondstone.com/urbanexplorations)

- **/config/**  
  Configuration files for database connection and security mechanisms.

  - **config.php**  
    Database credentials and connection settings.

  - **database.php**  
    Singleton-pattern PDO connection handler.

  - **csrf.php**  
    CSRF token generation and verification, including timing tokens for bot prevention.

- **/classes/**  
  Core business logic and repository classes.

  - **AdminAuth.php**  
    Administrative authentication with session management and TOTP-based 2FA enforcement.

  - **CommentRepository.php**  
    Data access layer for reading and writing comments.

  - **EmailNotifier.php**  
    Email notification system for new comments.

  - **LikeRepository.php**  
    Handles comment likes with duplicate click prevention.

  - **RateLimiter.php**  
    Fixed rate limiter.

  - **Totp.php**  
    Time-based One-Time Password (TOTP) generation and verification for 2FA.

- **/public/**  
  Public-facing endpoints for comment functionality.

  - **comments.php**  
    JSON endpoint returning all visible comments for a given page.

  - **comment-submit.php**  
    Handles new comment and reply submissions with validation, spam detection, and rate limiting.

  - **comment-like.php**  
    Processes like requests with rate limiting applied.

  - **comment-notify.php**  
    Sends admin email notifications after successful comment submission.

- **/admin/**  
  Admin interface for moderation.

  - **index.php**  
    Dashboard displaying comments with moderation control.

  - **login.php**  
    Login handler.

  - **logout.php**  
    Session destruction and logout handler.

  - **delete-comment.php**  
    Handles comment deletion including all nested replies.

- **Standalone UI Files**  
  Frontend files used for demo and integration.

  - **example.php**  
    Demo UI page showing comment system integration.

  - **ugc.css**  
    Stylesheet for the comment UI.

  - **ugc.js**  
    JavaScript connecting the UI to the public endpoints.

## All Changes Made

### Initial Core Features (2026)

1. **Threaded Comments**
   - Unlimited nested reply depth.

2. **Spam Detection**
   - Pattern-based filtering for SQL injection and XSS attempts.

3. **Rate Limiting**
   - Per-IP and per-session limits for comments and likes.

4. **Admin Panel**
   - Centralized moderation interface.

5. **2FA Authentication**
   - TOTP-based two-factor authentication for admin accounts.

6. **CSRF and Bot Protection**
   - Token-based CSRF protection.
   - Timing tokens to detect automated submissions.

7. **Email Notifications**
   - Automatic alerts for new comment submissions.

### Portfolio Modifications

1. **Security Obfuscation**
   - Queries, credentials, and spam detection patterns obfuscated for public release.
