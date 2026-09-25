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
  List<MemberNotification> notifications = [];
  int unreadNotifications = 0;
  bool notificationsBusy = false;
  String? notificationsError;
  String? error;
  bool busy = false;
  Future<void> restore() async {
    try {
      final token = await api.token();
      if (token == null) {
        session = SessionState.guest;
        notifyListeners();
        return;
      }
      await loadHome().timeout(const Duration(seconds: 4));
      session = SessionState.authenticated;
    } catch (_) {
      try {
        await api.clearToken();
      } catch (_) {}
      session = SessionState.guest;
    } finally {
      notifyListeners();
    }
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
    unreadNotifications = r.data['data']['unread_notifications'] ?? 0;
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

  Future<void> loadNotifications() async {
    notificationsBusy = true;
    notificationsError = null;
    notifyListeners();
    try {
      final r = await api.dio.get('/member/notifications');
      final data = r.data['data'];
      notifications = ((data['items']['data'] ?? []) as List)
          .map((e) => MemberNotification.fromJson(e))
          .toList();
      unreadNotifications = data['unread_count'] ?? 0;
    } catch (e) {
      notificationsError = api.message(e);
    } finally {
      notificationsBusy = false;
      notifyListeners();
    }
  }

  Future<void> readNotification(MemberNotification item) async {
    if (!item.unread) return;
    await api.dio.patch('/member/notifications/${item.id}/read');
    await loadNotifications();
  }

  Future<void> readAllNotifications() async {
    await api.dio.patch('/member/notifications/read-all');
    await loadNotifications();
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
