# Technolife Membership Mobile

Aplikasi member Flutter untuk Android dan iOS. API base URL dikonfigurasi satu kali
di `ApiClient`. Default emulator Android adalah `http://10.0.2.2:8000/api`, sedangkan
iOS simulator memakai `http://127.0.0.1:8000/api`.

Override URL saat menjalankan aplikasi:

```bash
flutter run --dart-define=API_BASE_URL=https://api.example.com/api
```

Untuk produksi gunakan HTTPS. Login member menggunakan OTP dan token disimpan di
secure storage perangkat.

## Getting Started

This project is a starting point for a Flutter application.

A few resources to get you started if this is your first Flutter project:

- [Lab: Write your first Flutter app](https://docs.flutter.dev/get-started/codelab)
- [Cookbook: Useful Flutter samples](https://docs.flutter.dev/cookbook)

For help getting started with Flutter development, view the
[online documentation](https://docs.flutter.dev/), which offers tutorials,
samples, guidance on mobile development, and a full API reference.
