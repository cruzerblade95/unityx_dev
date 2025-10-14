# Enable PHP GMP Extension for Web3 Integration

The Web3 blockchain integration requires the PHP GMP (GNU Multiple Precision) extension for cryptographic operations.

## Steps to Enable GMP Extension:

### 1. Open PHP Configuration File

Open this file in a text editor (as Administrator):

```
C:\php-8.4.10-Win32-vs17-x64\php.ini
```

### 2. Find and Enable GMP Extension

Search for the line (press Ctrl+F):

```
;extension=gmp
```

Remove the semicolon (`;`) at the beginning to enable it:

```
extension=gmp
```

If the line doesn't exist, add it in the extensions section:

```ini
extension=gmp
```

### 3. Save the File

Save the `php.ini` file.

### 4. Restart Services

If you're using:

- **Laravel Artisan**: Stop and restart `php artisan serve`
- **Apache**: Restart Apache service
- **Nginx + PHP-FPM**: Restart PHP-FPM service

### 5. Verify Installation

Run this command to verify GMP is enabled:

```bash
php -m | findstr gmp
```

You should see `gmp` in the output.

### 6. Continue with Web3 Installation

After enabling GMP, run:

```bash
composer require kornrunner/ethereum-offline-raw-tx
```

## Why is GMP Required?

GMP is required for:

- Signing blockchain transactions with private keys
- Elliptic curve cryptography (secp256k1)
- Large number arithmetic for blockchain operations
- Secure transaction generation

## Troubleshooting

**If GMP still doesn't load:**

1. Make sure you edited the correct `php.ini` file (check with `php --ini`)
2. Ensure no spaces before `extension=gmp`
3. Make sure `php_gmp.dll` exists in `C:\php-8.4.10-Win32-vs17-x64\ext\`
4. Restart your terminal/command prompt after changes

**If php_gmp.dll is missing:**
You may need to reinstall PHP or download the extension separately from:
https://windows.php.net/downloads/
