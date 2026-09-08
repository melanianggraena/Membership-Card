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
  final String title, description, terms, status, discountType;
  final double discountValue;
  final String? imageUrl;
  final DateTime? start, end;
  Promo({
    required this.id,
    required this.title,
    required this.description,
    required this.terms,
    required this.status,
    required this.discountType,
    required this.discountValue,
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
    discountType: j['discount_type'] ?? 'percentage',
    discountValue: double.tryParse('${j['discount_value'] ?? 0}') ?? 0,
    imageUrl: j['image_url'],
    start: DateTime.tryParse('${j['start_date'] ?? ''}'),
    end: DateTime.tryParse('${j['end_date'] ?? ''}'),
  );
  String get discountLabel => discountType == 'percentage'
      ? '${discountValue.toStringAsFixed(discountValue % 1 == 0 ? 0 : 2)}%'
      : 'Rp ${discountValue.toStringAsFixed(0)}';
}

class MemberNotification {
  final String id, title, description, category;
  final DateTime? createdAt, readAt;
  MemberNotification({
    required this.id,
    required this.title,
    required this.description,
    required this.category,
    this.createdAt,
    this.readAt,
  });
  factory MemberNotification.fromJson(Map<String, dynamic> j) {
    final data = Map<String, dynamic>.from(j['data'] ?? {});
    return MemberNotification(
      id: '${j['id'] ?? ''}',
      title: data['title'] ?? 'Notifikasi',
      description: data['description'] ?? '',
      category: data['category'] ?? 'system',
      createdAt: DateTime.tryParse('${j['created_at'] ?? ''}'),
      readAt: DateTime.tryParse('${j['read_at'] ?? ''}'),
    );
  }
  bool get unread => readAt == null;
}

class Tx {
  final int id;
  final String code, type, status;
  final double originalAmount, discount, amount, before, after;
  final DateTime? date;
  final String? room, outlet, promo;
  Tx({
    required this.id,
    required this.code,
    required this.type,
    required this.status,
    required this.originalAmount,
    required this.discount,
    required this.amount,
    required this.before,
    required this.after,
    this.date,
    this.room,
    this.outlet,
    this.promo,
  });
  factory Tx.fromJson(Map<String, dynamic> j) => Tx(
    id: j['id'] ?? 0,
    code: j['transaction_code'] ?? '',
    type: j['transaction_type'] ?? '',
    status: j['status'] ?? '',
    originalAmount:
        double.tryParse('${j['original_amount'] ?? j['amount']}') ?? 0,
    discount: double.tryParse('${j['discount_amount'] ?? 0}') ?? 0,
    amount: double.tryParse('${j['amount']}') ?? 0,
    before: double.tryParse('${j['balance_before']}') ?? 0,
    after: double.tryParse('${j['balance_after']}') ?? 0,
    date: DateTime.tryParse('${j['created_at'] ?? ''}'),
    room: j['room']?['room_name'],
    outlet: j['outlet']?['outlet_name'],
    promo: j['promo']?['title'],
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
