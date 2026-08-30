import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiClient {
  static const _storage = FlutterSecureStorage();
  static String get defaultBaseUrl => Platform.isAndroid
      ? 'http://192.168.1.6:8000/api'
      : 'http://127.0.0.1:8000/api';
  late final Dio dio;
  Future<void> Function()? onUnauthorized;
  ApiClient({String? baseUrl}) {
    dio = Dio(
      BaseOptions(
        baseUrl:
            const String.fromEnvironment(
              'API_BASE_URL',
              defaultValue: '',
            ).isNotEmpty
            ? const String.fromEnvironment('API_BASE_URL')
            : (baseUrl ?? defaultBaseUrl),
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 15),
        headers: {'Accept': 'application/json'},
      ),
    );
    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (o, h) async {
          final token = await _storage.read(key: 'member_token');
          if (token != null) o.headers['Authorization'] = 'Bearer $token';
          h.next(o);
        },
        onError: (e, h) async {
          if (e.response?.statusCode == 401) await clearToken();
          if (e.response?.statusCode == 401) await onUnauthorized?.call();
          h.next(e);
        },
      ),
    );
  }
  Future<String?> token() => _storage.read(key: 'member_token');
  Future<void> saveToken(String value) =>
      _storage.write(key: 'member_token', value: value);
  Future<void> clearToken() => _storage.delete(key: 'member_token');
  String message(Object e) {
    if (e is DioException) {
      if (e.response?.statusCode == 429)
        return 'Terlalu banyak permintaan. Coba lagi nanti.';
      if (e.response?.statusCode == 401) return 'Sesi Anda telah berakhir.';
      if (e.response?.data is Map && e.response?.data['message'] != null)
        return e.response?.data['message'];
      if (e.type == DioExceptionType.connectionError ||
          e.type == DioExceptionType.connectionTimeout)
        return 'Periksa koneksi internet Anda.';
    }
    return 'Terjadi kesalahan. Silakan coba lagi.';
  }
}
