import 'package:flutter/foundation.dart';
import '../core/api_client.dart';
import '../models/models.dart';

enum SessionState { loading, guest, authenticated }

class AppState extends ChangeNotifier {
  final ApiClient api;
  AppState(this.api) {
    api.onUnauthorized = () async {
      member = null;
      session = SessionState.guest;
      notifyListeners();
    };
  }
  SessionState session = SessionState.loading;
  Member? member;
  List<Promo> promos = [];
  List<Tx> transactions = [];
  List<AccessItem> accesses = [];
  String? error;
  bool busy = false;
  Future<void> restore() async {
    final token = await api.token();
    if (token == null) {
      session = SessionState.guest;
      notifyListeners();
      return;
    }
    try {
      await loadHome();
      session = SessionState.authenticated;
    } catch (_) {
      await api.clearToken();
      session = SessionState.guest;
    }
    notifyListeners();
  }

  Future<String?> requestOtp(String phone) async {
    busy = true;
    error = null;
    notifyListeners();
    try {
      final r = await api.dio.post(
        '/member/login/request-otp',
        data: {'phone': phone},
      );
      return r.data['data']?['debug_otp'];
    } catch (e) {
      error = api.message(e);
      rethrow;
    } finally {
      busy = false;
      notifyListeners();
    }
  }

  Future<void> verifyOtp(String phone, String otp) async {
    busy = true;
    error = null;
    notifyListeners();
    try {
      final r = await api.dio.post(
        '/member/login/verify-otp',
        data: {'phone': phone, 'otp': otp},
      );
      await api.saveToken(r.data['data']['token']);
      member = Member.fromJson(r.data['data']['member']);
      session = SessionState.authenticated;
      await loadHome();
    } catch (e) {
      error = api.message(e);
      rethrow;
    } finally {
      busy = false;
      notifyListeners();
    }
  }

  Future<void> loadHome() async {
    final r = await api.dio.get('/member/home');
    member = Member.fromJson(r.data['data']['member']);
    promos = (r.data['data']['promos'] as List)
        .map((e) => Promo.fromJson(e))
        .toList();
    notifyListeners();
  }

  Future<void> loadHistory() async {
    try {
      final rs = await Future.wait([
        api.dio.get('/member/transactions'),
        api.dio.get('/member/access-history'),
      ]);
      transactions = ((rs[0].data['data']['data'] ?? []) as List)
          .map((e) => Tx.fromJson(e))
          .toList();
      accesses = ((rs[1].data['data']['data'] ?? []) as List)
          .map((e) => AccessItem.fromJson(e))
          .toList();
      error = null;
    } catch (e) {
      error = api.message(e);
    }
    notifyListeners();
  }

  Future<void> loadPromos() async {
    try {
      final r = await api.dio.get('/member/promos');
      promos = (r.data['data'] as List).map((e) => Promo.fromJson(e)).toList();
    } catch (e) {
      error = api.message(e);
    }
    notifyListeners();
  }

  Future<void> updateProfile(String name, String email, String phone) async {
    busy = true;
    notifyListeners();
    try {
      final r = await api.dio.put(
        '/member/profile',
        data: {'full_name': name, 'email': email, 'phone': phone},
      );
      member = Member.fromJson(r.data['data']);
    } catch (e) {
      error = api.message(e);
      rethrow;
    } finally {
      busy = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    try {
      await api.dio.post('/member/logout');
    } catch (_) {}
    await api.clearToken();
    member = null;
    session = SessionState.guest;
    notifyListeners();
  }
}
