class Member {
  final int id;
  final String code, name, phone, email, status;
  final double balance;
  final DateTime? expiredAt, lastUsed;
  Member({
    required this.id,
    required this.code,
    required this.name,
    required this.phone,
    required this.email,
    required this.status,
    required this.balance,
    this.expiredAt,
    this.lastUsed,
  });
  factory Member.fromJson(Map<String, dynamic> j) => Member(
    id: j['id'] ?? 0,
    code: j['member_code'] ?? '',
    name: j['full_name'] ?? '',
    phone: j['phone'] ?? '',
    email: j['email'] ?? '',
    status: j['status'] ?? 'inactive',
    balance: double.tryParse('${j['balance'] ?? 0}') ?? 0,
    expiredAt: DateTime.tryParse('${j['expired_at'] ?? ''}'),
    lastUsed: DateTime.tryParse('${j['last_used'] ?? ''}'),
  );
  bool get active =>
      status == 'active' &&
      (expiredAt == null || expiredAt!.isAfter(DateTime.now()));
}

class Promo {
  final int id;
  final String title, description, terms, status;
  final String? imageUrl;
  final DateTime? start, end;
  Promo({
    required this.id,
    required this.title,
    required this.description,
    required this.terms,
    required this.status,
    this.imageUrl,
    this.start,
    this.end,
  });
  factory Promo.fromJson(Map<String, dynamic> j) => Promo(
    id: j['id'] ?? 0,
    title: j['title'] ?? '',
    description: j['description'] ?? '',
    terms: j['terms'] ?? '',
    status: j['status'] ?? '',
    imageUrl: j['image_url'],
    start: DateTime.tryParse('${j['start_date'] ?? ''}'),
    end: DateTime.tryParse('${j['end_date'] ?? ''}'),
  );
}

class Tx {
  final int id;
  final String type, status;
  final double amount, before, after;
  final DateTime? date;
  final String? room;
  Tx({
    required this.id,
    required this.type,
    required this.status,
    required this.amount,
    required this.before,
    required this.after,
    this.date,
    this.room,
  });
  factory Tx.fromJson(Map<String, dynamic> j) => Tx(
    id: j['id'] ?? 0,
    type: j['transaction_type'] ?? '',
    status: j['status'] ?? '',
    amount: double.tryParse('${j['amount']}') ?? 0,
    before: double.tryParse('${j['balance_before']}') ?? 0,
    after: double.tryParse('${j['balance_after']}') ?? 0,
    date: DateTime.tryParse('${j['created_at'] ?? ''}'),
    room: j['room']?['room_name'],
  );
}

class AccessItem {
  final int id;
  final String uid, status, reason, room;
  final DateTime? date;
  AccessItem({
    required this.id,
    required this.uid,
    required this.status,
    required this.reason,
    required this.room,
    this.date,
  });
  factory AccessItem.fromJson(Map<String, dynamic> j) => AccessItem(
    id: j['id'] ?? 0,
    uid: j['uid'] ?? '',
    status: j['access_status'] ?? '',
    reason: j['reason'] ?? '',
    room: j['room']?['room_name'] ?? '-',
    date: DateTime.tryParse('${j['scanned_at'] ?? ''}'),
  );
}
