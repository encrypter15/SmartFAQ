# Smart FAQ Generator
Author: Rick Hayes  
Version: 1.0  
License: MIT  

## Description
A robust web app that generates FAQs from user questions using the xAI API. It’s secure, reliable, and ready to impress your visitors.

## Requirements
- PHP 7.4+
- MySQL 5.7+
- Apache server
- `curl` extension for PHP
- xAI API key

## Setup
1. Create a MySQL database `faq_db` with table:
   ```sql
   CREATE TABLE questions (
       id INT AUTO_INCREMENT PRIMARY KEY,
       text TEXT NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```
2. Copy `config.php` and update with your database credentials and API key (or set `XAI_API_KEY` in your environment).
3. Place files in your Apache web root (e.g., `/var/www/html`).
4. Ensure `.htaccess` denies access to `config.php`:
   ```
   <Files "config.php">
       Order Allow,Deny
       Deny from all
   </Files>
   ```

## Usage
- Visit the page, submit questions, and watch the FAQ grow.
- Errors are logged to your PHP error log for debugging.

## Security Notes
- API key is safely stored (use environment variables for extra points!).
- Input is sanitized to keep the gremlins out.
- Session-based FAQ caching prevents unnecessary API calls.

## License
This project is licensed under the MIT License.

## Notes
- If the FAQ sounds like a robot wrote it, it’s because one did! But don’t worry, it’s a polite robot.
- Keep `config.php` out of prying eyes, or the FAQ fairy might get cranky.
```

---

#### Enhancements
- **Robustness**: Added PDO with error handling, cURL timeout, and session caching for FAQs.
- **Security**: API key in `config.php` or environment, input sanitization, and HTML escaping with `htmlspecialchars`.
- **Functionality**: Stores questions in MySQL, limits to last 10 for context, and includes basic CSS/JavaScript for a polished UI.
- **Humor**: Comments like “The AI is sulking” and notes about the “FAQ fairy” keep it fun yet trustworthy.

