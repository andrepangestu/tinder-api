# Mobile Access Guide

## ✅ API Ready untuk Mobile Access

API sudah dikonfigurasi dengan CORS yang benar untuk diakses dari mobile devices.

---

## 🌐 Base URL

```
https://andrepangestu.com/api
```

---

## 📱 CORS Configuration

API menggunakan **wildcard CORS** yang memungkinkan akses dari semua origins:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH
Access-Control-Allow-Headers: *
```

---

## 🔑 SSL Certificate

- ✅ Valid SSL Certificate dari Sectigo
- ✅ Expires: October 8, 2026
- ✅ Trusted by all major browsers and mobile platforms

---

## 📋 Available Endpoints

### 1. Test Endpoint
```
GET https://andrepangestu.com/api/test
```

Response:
```json
{
  "message": "API is working!",
  "timestamp": "2025-11-07T22:35:34.863135Z",
  "people_count": 100
}
```

### 2. Get Recommended People
```
GET https://andrepangestu.com/api/people/recommended?page=1
```

### 3. Get All People
```
GET https://andrepangestu.com/api/people?page=1
```

### 4. Get Person Details
```
GET https://andrepangestu.com/api/people/{id}
```

### 5. Like a Person
```
POST https://andrepangestu.com/api/people/{id}/like
```

### 6. Dislike a Person
```
POST https://andrepangestu.com/api/people/{id}/dislike
```

### 7. Get Activity History
```
GET https://andrepangestu.com/api/people/activities?page=1
```

---

## 📲 Testing dari Mobile

### React Native / Expo
```javascript
const response = await fetch('https://andrepangestu.com/api/test', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
  },
});
const data = await response.json();
console.log(data);
```

### Flutter
```dart
final response = await http.get(
  Uri.parse('https://andrepangestu.com/api/test'),
  headers: {'Content-Type': 'application/json'},
);
print(response.body);
```

### Swift (iOS)
```swift
let url = URL(string: "https://andrepangestu.com/api/test")!
let task = URLSession.shared.dataTask(with: url) { data, response, error in
    if let data = data {
        print(String(data: data, encoding: .utf8)!)
    }
}
task.resume()
```

### Kotlin (Android)
```kotlin
val url = URL("https://andrepangestu.com/api/test")
val connection = url.openConnection() as HttpURLConnection
val response = connection.inputStream.bufferedReader().readText()
println(response)
```

---

## 🧪 Testing dengan cURL

### Test from command line (simulate mobile)
```bash
curl -H "Origin: https://mobile-app.example.com" \
     -H "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)" \
     https://andrepangestu.com/api/test
```

### Test GET request
```bash
curl https://andrepangestu.com/api/people/recommended
```

### Test POST request
```bash
curl -X POST https://andrepangestu.com/api/people/1/like \
     -H "Content-Type: application/json"
```

---

## ⚠️ Common Issues & Solutions

### Issue 1: SSL Certificate Error
**Symptom:** "SSL certificate problem" atau "untrusted certificate"

**Solution:** Certificate sudah valid. Pastikan device/app tidak menggunakan custom SSL validation.

### Issue 2: CORS Error
**Symptom:** "Access-Control-Allow-Origin" error

**Solution:** Sudah fixed! CORS headers sudah dikonfigurasi dengan benar.

### Issue 3: 404 Not Found
**Symptom:** Endpoint returns 404

**Solution:** 
- Cek URL sudah benar: `https://andrepangestu.com/api/...`
- Jangan lupa prefix `/api`

### Issue 4: 500 Internal Server Error
**Symptom:** Server returns 500

**Solution:** Check server logs:
```bash
ssh root@andrepangestu.com
cd /var/www/tinder-api
docker compose logs --tail=50 app
```

---

## 🔍 Debugging

### Check if API is accessible
```bash
curl -I https://andrepangestu.com/api/test
```

Should return:
```
HTTP/1.1 200 OK
Access-Control-Allow-Origin: *
Content-Type: application/json
```

### Check CORS headers
```bash
curl -H "Origin: https://example.com" \
     -I https://andrepangestu.com/api/test
```

Should include:
```
Access-Control-Allow-Origin: *
Access-Control-Expose-Headers: Authorization, Content-Type, X-Requested-With
```

---

## 📊 Response Format

All endpoints return JSON in this format:

**Success Response:**
```json
{
  "status": "success",
  "message": "Operation successful",
  "data": { ... }
}
```

**Error Response:**
```json
{
  "status": "error",
  "message": "Error message here",
  "errors": { ... }
}
```

---

## 🚀 Performance Tips

1. **Pagination**: Gunakan parameter `?page=1` untuk large datasets
2. **Caching**: Response di-cache otomatis oleh browser/app
3. **Compression**: Nginx sudah enable gzip compression
4. **Keep-Alive**: Connection keep-alive enabled

---

## 📞 Support

Jika ada issue:

1. Check server logs (see commands in `DEBUG_COMMANDS.md`)
2. Test endpoint dengan cURL dulu
3. Verify CORS headers ada di response
4. Check SSL certificate validity

---

## ✅ Verification Checklist

- [x] SSL Certificate valid dan trusted
- [x] CORS headers configured correctly
- [x] Firewall allows HTTP/HTTPS traffic
- [x] All endpoints accessible
- [x] JSON responses working
- [x] Mobile-friendly CORS policy
- [x] Error handling implemented
