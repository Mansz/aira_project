# Panduan Konfigurasi CORS & Sanctum untuk Development Lokal

## 1. Konfigurasi .env yang Diperlukan

Pastikan file `.env` Anda memiliki variabel berikut:

```env
APP_ENV=local
APP_URL=http://localhost:8000

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8000,127.0.0.1:3000,127.0.0.1:8000

# Session Configuration
SESSION_DRIVER=file
SESSION_DOMAIN=localhost
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE_COOKIE=lax

# Frontend URL (opsional)
FRONTEND_URL=http://localhost:3000
```

## 2. Verifikasi Konfigurasi File

### A. config/cors.php ✅ SUDAH BENAR
- `supports_credentials` = true
- `allowed_origins` sudah include localhost:3000
- Paths sudah include 'api/*' dan 'sanctum/csrf-cookie'

### B. config/sanctum.php ✅ SUDAH BENAR
- Stateful domains sudah include semua localhost variants
- Guard menggunakan 'web'

### C. config/session.php ✅ SUDAH BENAR
- Secure cookie menggunakan environment check
- Same-site cookie configurable via env

## 3. Testing Endpoints

### A. Test dengan Browser/Postman

1. **Get CSRF Cookie**
   ```
   GET http://localhost:8000/sanctum/csrf-cookie
   ```
   - Harus return 204 No Content
   - Check cookies: harus ada XSRF-TOKEN dan laravel_session

2. **Login Request**
   ```
   POST http://localhost:8000/login
   Content-Type: application/json
   
   {
     "email": "admin@example.com",
     "password": "password"
   }
   ```
   - Include cookies dari step 1
   - Harus return user data atau success response

3. **Test Protected Route**
   ```
   GET http://localhost:8000/api/user
   ```
   - Include cookies dari login
   - Harus return authenticated user data

### B. Test dengan Frontend JavaScript

```javascript
// Setup axios dengan credentials
axios.defaults.withCredentials = true;
axios.defaults.baseURL = 'http://localhost:8000';

// 1. Get CSRF Cookie
const getCsrfCookie = async () => {
  try {
    await axios.get('/sanctum/csrf-cookie');
    console.log('CSRF cookie obtained');
  } catch (error) {
    console.error('CSRF Error:', error);
  }
};

// 2. Login
const login = async (email, password) => {
  try {
    await getCsrfCookie(); // Get CSRF first
    
    const response = await axios.post('/login', {
      email,
      password
    }, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      }
    });
    
    console.log('Login successful:', response.data);
    return response.data;
  } catch (error) {
    console.error('Login Error:', error.response?.data);
  }
};

// 3. Test Protected Route
const getUser = async () => {
  try {
    const response = await axios.get('/api/user');
    console.log('User data:', response.data);
    return response.data;
  } catch (error) {
    console.error('Get User Error:', error.response?.data);
  }
};

// Usage
login('admin@example.com', 'password')
  .then(() => getUser());
```

## 4. Troubleshooting Common Issues

### A. CORS Errors
- Pastikan frontend origin (localhost:3000) ada di allowed_origins
- Pastikan supports_credentials = true
- Check browser console untuk error detail

### B. Cookie Issues
- Pastikan SESSION_SECURE_COOKIE=false untuk local
- Check SESSION_DOMAIN=localhost (bukan null)
- Pastikan withCredentials: true di frontend

### C. CSRF Token Issues
- Selalu panggil /sanctum/csrf-cookie sebelum login
- Pastikan X-XSRF-TOKEN header dikirim otomatis oleh axios

## 5. Commands untuk Testing

### Start Laravel Server
```bash
php artisan serve --host=localhost --port=8000
```

### Clear Cache (jika ada masalah)
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Check Current Config
```bash
php artisan config:show cors
php artisan config:show sanctum
php artisan config:show session
```

## 6. Checklist Verifikasi

- [ ] APP_ENV=local di .env
- [ ] SANCTUM_STATEFUL_DOMAINS include localhost:3000
- [ ] SESSION_SECURE_COOKIE=false
- [ ] Frontend bisa akses /sanctum/csrf-cookie
- [ ] Login berhasil dan dapat cookies
- [ ] Protected routes bisa diakses dengan cookies
- [ ] No CORS errors di browser console

## 7. Network Tab Verification

Di browser DevTools > Network tab, pastikan:

1. **CSRF Request**: Status 204, Set-Cookie headers ada
2. **Login Request**: Status 200/201, cookies dikirim di request headers
3. **API Requests**: Cookies otomatis included di request headers

Jika semua langkah ini berhasil, konfigurasi CORS dan Sanctum Anda sudah benar untuk development lokal.
