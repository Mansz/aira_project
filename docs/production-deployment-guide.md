# Production Deployment Guide - CORS & Sanctum Configuration

## Overview
This guide explains how to properly configure CORS and Laravel Sanctum for production deployment to resolve authentication and cookie issues.

## Common Issues Resolved
- ❌ CORS errors when accessing API from frontend
- ❌ Sanctum cookies not being sent/received
- ❌ Authentication failing in production
- ❌ "blocked sanctum cookie" errors in browser console

## Configuration Files Updated

### 1. CORS Configuration (`config/cors.php`)
```php
'allowed_origins' => array_filter([
    // Local Development
    env('APP_ENV') === 'local' ? 'http://localhost:3000' : null,
    env('APP_ENV') === 'local' ? 'http://localhost:5176' : null,
    env('APP_ENV') === 'local' ? 'http://localhost:8000' : null,
    env('APP_ENV') === 'local' ? 'http://127.0.0.1:3000' : null,
    env('APP_ENV') === 'local' ? 'http://127.0.0.1:5176' : null,
    
    // Production URLs - Replace with your actual domains
    'https://frontend.example.com',
    'https://api.example.com',
    env('FRONTEND_URL'),
    env('ADMIN_PANEL_URL'),
]),
```

**Key Points:**
- ✅ `supports_credentials` is set to `true` (required for cookies)
- ✅ Includes both development and production origins
- ✅ Uses HTTPS for production domains

### 2. Sanctum Configuration (`config/sanctum.php`)
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s%s%s',
    'localhost,localhost:3000,localhost:5176,localhost:8000,127.0.0.1,127.0.0.1:3000,127.0.0.1:5176,127.0.0.1:8000',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : '',
    env('FRONTEND_URL') ? ','.parse_url(env('FRONTEND_URL'), PHP_URL_HOST) : '',
    ',frontend.example.com,api.example.com'
))),
```

**Key Points:**
- ✅ Includes production domains in stateful domains
- ✅ Supports both local development and production
- ✅ Uses environment variables for flexibility

### 3. Session Configuration (`config/session.php`)
```php
'secure' => env('SESSION_SECURE_COOKIE', env('APP_ENV') === 'production'),
'same_site' => env('SESSION_SAME_SITE_COOKIE', 'lax'),
```

**Key Points:**
- ✅ Automatically enables secure cookies in production
- ✅ Configurable SameSite policy
- ✅ Proper domain configuration support

## Production Environment Variables

Copy `.env.production.example` to `.env` on your production server and update these critical values:

```env
# Application
APP_ENV=production
APP_URL=https://api.example.com
FRONTEND_URL=https://frontend.example.com

# Session & Cookies
SESSION_DOMAIN=.example.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE_COOKIE=lax

# Sanctum
SANCTUM_STATEFUL_DOMAINS=frontend.example.com,api.example.com
```

## Frontend Configuration

### Axios Configuration (React/Vue/etc.)
```javascript
// Configure axios to include credentials
axios.defaults.withCredentials = true;
axios.defaults.baseURL = 'https://api.example.com';

// Before making authenticated requests, get CSRF token
await axios.get('/sanctum/csrf-cookie');

// Then make your login request
const response = await axios.post('/api/login', {
    email: 'user@example.com',
    password: 'password'
});
```

### Fetch API Configuration
```javascript
// Configure fetch to include credentials
const response = await fetch('https://api.example.com/api/login', {
    method: 'POST',
    credentials: 'include', // Important!
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    body: JSON.stringify({
        email: 'user@example.com',
        password: 'password'
    })
});
```

## Deployment Checklist

### Before Deployment
- [ ] Update domain names in `config/cors.php`
- [ ] Update domain names in `config/sanctum.php`
- [ ] Create production `.env` file with correct values
- [ ] Generate new `APP_KEY` using `php artisan key:generate`
- [ ] Set `APP_DEBUG=false` in production

### After Deployment
- [ ] Clear configuration cache: `php artisan config:clear`
- [ ] Cache configuration: `php artisan config:cache`
- [ ] Clear route cache: `php artisan route:clear`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Test CSRF cookie endpoint: `GET /sanctum/csrf-cookie`
- [ ] Test login functionality from frontend

## Troubleshooting

### 1. CORS Errors
**Symptoms:** Browser console shows CORS policy errors
**Solutions:**
- Verify frontend domain is in `allowed_origins`
- Check that `supports_credentials` is `true`
- Ensure API responses include proper CORS headers

### 2. Cookies Not Being Set
**Symptoms:** No cookies in browser after login
**Solutions:**
- Verify `SESSION_DOMAIN` is set correctly (use `.example.com` for subdomains)
- Ensure `SESSION_SECURE_COOKIE=true` in production
- Check that frontend includes `credentials: 'include'` in requests

### 3. Authentication Failing
**Symptoms:** User appears logged in but API returns 401
**Solutions:**
- Verify domain is in `SANCTUM_STATEFUL_DOMAINS`
- Check that CSRF token is being sent with requests
- Ensure session configuration is correct

### 4. Mixed Content Errors
**Symptoms:** HTTPS frontend can't connect to HTTP API
**Solutions:**
- Ensure API is served over HTTPS in production
- Update all URLs to use HTTPS protocol
- Configure SSL certificate properly

## Testing Commands

```bash
# Test CSRF cookie endpoint
curl -X GET https://api.example.com/sanctum/csrf-cookie \
  -H "Origin: https://frontend.example.com" \
  -v

# Test CORS preflight
curl -X OPTIONS https://api.example.com/api/login \
  -H "Origin: https://frontend.example.com" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type" \
  -v

# Check Laravel configuration
php artisan config:show cors
php artisan config:show sanctum
php artisan config:show session
```

## Security Considerations

1. **Always use HTTPS in production**
2. **Set secure cookies** (`SESSION_SECURE_COOKIE=true`)
3. **Use proper SameSite policy** (`lax` or `strict`)
4. **Limit CORS origins** to only necessary domains
5. **Keep APP_KEY secret** and unique per environment
6. **Disable debug mode** in production (`APP_DEBUG=false`)

## Additional Resources

- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Laravel CORS Documentation](https://github.com/fruitcake/laravel-cors)
- [MDN CORS Guide](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)
- [Cookie SameSite Attribute](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite)
