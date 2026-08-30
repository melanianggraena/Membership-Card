import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../models/models.dart';
import '../providers/app_state.dart';

const crimson = Color(0xffdc143c),
    ink = Color(0xff121212),
    muted = Color(0xff656464);
final rupiah = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp',
      decimalDigits: 0,
    ),
    dateFmt = DateFormat('dd MMM yyyy');
String fd(DateTime? d) => d == null ? '-' : dateFmt.format(d);
void open(BuildContext c, Widget page) =>
    Navigator.push(c, MaterialPageRoute(builder: (_) => page));

class RootScreen extends StatelessWidget {
  const RootScreen({super.key});
  @override
  Widget build(BuildContext context) {
    final s = context.watch<AppState>();
    if (s.session == SessionState.loading) {
      return const SplashScreen();
    }
    if (s.session == SessionState.guest) {
      return const LoginScreen();
    }
    return const MainShell();
  }
}

class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});
  @override
  Widget build(BuildContext context) => const Scaffold(
    backgroundColor: crimson,
    body: SafeArea(
      child: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            CircleAvatar(
              radius: 43,
              backgroundColor: Colors.white,
              child: Text(
                'T',
                style: TextStyle(
                  color: crimson,
                  fontSize: 42,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ),
            SizedBox(height: 20),
            Text(
              'Technolife',
              style: TextStyle(
                color: Colors.white,
                fontSize: 30,
                fontWeight: FontWeight.w800,
              ),
            ),
            SizedBox(height: 8),
            Text(
              'Membership',
              style: TextStyle(color: Colors.white70, letterSpacing: 2),
            ),
          ],
        ),
      ),
    ),
  );
}

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginState();
}

class _LoginState extends State<LoginScreen> {
  final phone = TextEditingController();

  Future<void> submit() async {
    if (phone.text.trim().isEmpty) {
      return;
    }

    try {
      final debug = await context.read<AppState>().requestOtp(
        phone.text.trim(),
      );

      if (!mounted) {
        return;
      }
      open(context, OtpScreen(phone: phone.text.trim(), debugOtp: debug));
    } catch (_) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(context.read<AppState>().error ?? 'Gagal')),
      );
    }
  }

  @override
  Widget build(BuildContext c) {
    final busy = c.watch<AppState>().busy;
    return Scaffold(
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Padding(
              padding: EdgeInsets.all(24),
              child: Text(
                'Technolife',
                style: TextStyle(
                  color: Color(0xffb1002c),
                  fontSize: 27,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ),
            const Spacer(),
            Padding(
              padding: const EdgeInsets.all(24),
              child: Card(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    children: [
                      const CircleAvatar(
                        radius: 34,
                        backgroundColor: Color(0xffffe5eb),
                        child: Icon(
                          Icons.person,
                          color: Color(0xffb1002c),
                          size: 38,
                        ),
                      ),
                      const SizedBox(height: 28),
                      const Text(
                        'Selamat Datang',
                        style: TextStyle(
                          fontSize: 28,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'Masuk ke Membership Technolife',
                        style: TextStyle(color: muted),
                      ),
                      const SizedBox(height: 34),
                      TextField(
                        controller: phone,
                        keyboardType: TextInputType.phone,
                        decoration: const InputDecoration(
                          labelText: 'No. Handphone',
                          hintText: '08xxxxxxxxxx',
                          prefixIcon: Icon(Icons.phone_android),
                          border: OutlineInputBorder(),
                        ),
                      ),
                      const SizedBox(height: 24),
                      FilledButton(
                        onPressed: busy ? null : submit,
                        style: FilledButton.styleFrom(
                          minimumSize: const Size.fromHeight(54),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(9),
                          ),
                        ),
                        child: busy
                            ? const SizedBox.square(
                                dimension: 22,
                                child: CircularProgressIndicator(
                                  color: Colors.white,
                                  strokeWidth: 2,
                                ),
                              )
                            : const Text(
                                'Masuk',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            const Spacer(),
          ],
        ),
      ),
    );
  }
}

class OtpScreen extends StatefulWidget {
  final String phone;
  final String? debugOtp;
  const OtpScreen({super.key, required this.phone, this.debugOtp});
  @override
  State<OtpScreen> createState() => _OtpState();
}

class _OtpState extends State<OtpScreen> {
  final otp = TextEditingController();

  Future<void> submit() async {
    try {
      await context.read<AppState>().verifyOtp(widget.phone, otp.text);
      if (!mounted) {
        return;
      }
      Navigator.popUntil(context, (r) => r.isFirst);
    } catch (_) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(context.read<AppState>().error ?? 'OTP gagal')),
      );
    }
  }

  @override
  Widget build(BuildContext c) => Scaffold(
    appBar: AppBar(title: const Text('Verifikasi OTP')),
    body: SafeArea(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            const SizedBox(height: 60),
            const CircleAvatar(
              radius: 38,
              backgroundColor: Color(0xffffe5eb),
              child: Icon(
                Icons.verified_user_outlined,
                color: crimson,
                size: 38,
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'Masukkan Kode OTP',
              style: TextStyle(fontSize: 25, fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 10),
            Text(
              'Kode 6 digit dikirim ke ${widget.phone}',
              textAlign: TextAlign.center,
              style: const TextStyle(color: muted),
            ),
            if (widget.debugOtp != null)
              Padding(
                padding: const EdgeInsets.only(top: 8),
                child: Text(
                  'Mode lokal: ${widget.debugOtp}',
                  style: const TextStyle(color: crimson),
                ),
              ),
            const SizedBox(height: 32),
            TextField(
              controller: otp,
              maxLength: 6,
              textAlign: TextAlign.center,
              keyboardType: TextInputType.number,
              style: const TextStyle(
                fontSize: 28,
                letterSpacing: 12,
                fontWeight: FontWeight.bold,
              ),
              decoration: const InputDecoration(
                counterText: '',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 20),
            FilledButton(
              onPressed: c.watch<AppState>().busy ? null : submit,
              style: FilledButton.styleFrom(
                minimumSize: const Size.fromHeight(52),
              ),
              child: const Text('Verifikasi'),
            ),
          ],
        ),
      ),
    ),
  );
}

class MainShell extends StatefulWidget {
  const MainShell({super.key});
  @override
  State<MainShell> createState() => _ShellState();
}

class _ShellState extends State<MainShell> {
  int index = 0;
  @override
  Widget build(BuildContext c) {
    final pages = [
      const HomeScreen(),
      const HistoryScreen(),
      const PromoScreen(),
      const ProfileScreen(),
    ];
    return Scaffold(
      body: IndexedStack(index: index, children: pages),
      bottomNavigationBar: NavigationBar(
        selectedIndex: index,
        onDestinationSelected: (v) => setState(() => index = v),
        indicatorColor: const Color(0xffffd9e1),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home),
            label: 'Beranda',
          ),
          NavigationDestination(icon: Icon(Icons.history), label: 'Riwayat'),
          NavigationDestination(
            icon: Icon(Icons.confirmation_number_outlined),
            selectedIcon: Icon(Icons.confirmation_number),
            label: 'Promo',
          ),
          NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person),
            label: 'Profil',
          ),
        ],
      ),
    );
  }
}

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});
  @override
  Widget build(BuildContext c) {
    final s = c.watch<AppState>(), m = s.member;
    if (m == null) {
      return const SkeletonPage();
    }
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Technolife',
          style: TextStyle(fontWeight: FontWeight.w800),
        ),
        actions: [
          IconButton(
            onPressed: () {},
            icon: const Badge(child: Icon(Icons.notifications_outlined)),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: s.loadHome,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Text(
              'Halo, ${m.name.split(' ').first} 👋',
              style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800),
            ),
            const Text(
              'Selamat datang di Technolife',
              style: TextStyle(color: muted),
            ),
            const SizedBox(height: 28),
            MembershipCard(
              member: m,
              onTap: () => open(c, MembershipScreen(member: m)),
            ),
            const SizedBox(height: 20),
            InfoCard(
              title: 'Saldo Deposit',
              value: rupiah.format(m.balance),
              action: 'Lihat Semua',
              onTap: () => open(c, BalanceScreen(member: m)),
            ),
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.symmetric(
                  vertical: 18,
                  horizontal: 8,
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    Quick(
                      Icons.credit_card,
                      'Kartu Saya',
                      () => open(c, MembershipScreen(member: m)),
                    ),
                    Quick(
                      Icons.account_balance_wallet_outlined,
                      'Saldo',
                      () => open(c, BalanceScreen(member: m)),
                    ),
                    Quick(
                      Icons.receipt_long_outlined,
                      'Transaksi',
                      () => open(c, const TransactionListScreen()),
                    ),
                    Quick(
                      Icons.meeting_room_outlined,
                      'Akses',
                      () => open(c, const AccessHistoryScreen()),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 28),
            const Text(
              'Promo & Penawaran',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 12),
            if (s.promos.isEmpty)
              const EmptyState(
                icon: Icons.local_offer_outlined,
                text: 'Belum ada promo aktif',
              )
            else
              SizedBox(
                height: 220,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemCount: s.promos.length,
                  separatorBuilder: (_, __) => const SizedBox(width: 12),
                  itemBuilder: (_, i) => SizedBox(
                    width: 310,
                    child: PromoCard(
                      promo: s.promos[i],
                      onTap: () =>
                          open(c, PromoDetailScreen(promo: s.promos[i])),
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class MembershipCard extends StatelessWidget {
  final Member member;
  final VoidCallback onTap;
  const MembershipCard({super.key, required this.member, required this.onTap});
  @override
  Widget build(BuildContext c) => InkWell(
    onTap: onTap,
    borderRadius: BorderRadius.circular(16),
    child: Column(
      children: [
        Container(
          height: 215,
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            gradient: const LinearGradient(
              colors: [Color(0xff1c1b1b), Color(0xff40000a)],
            ),
            boxShadow: const [
              BoxShadow(
                color: Colors.black26,
                blurRadius: 25,
                offset: Offset(0, 10),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'TECHNOLIFE',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 21,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  Icon(Icons.contactless_outlined, color: Colors.white70),
                ],
              ),
              const Spacer(),
              const Text(
                'MEMBER',
                style: TextStyle(
                  color: Colors.white60,
                  letterSpacing: 2,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                member.name,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 24,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    member.code,
                    style: const TextStyle(color: Colors.white),
                  ),
                  Text(
                    'Valid Until ${fd(member.expiredAt)}',
                    style: const TextStyle(color: Colors.white70, fontSize: 12),
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Icon(
              Icons.circle,
              size: 11,
              color: member.active ? const Color(0xff10b981) : Colors.red,
            ),
            const SizedBox(width: 8),
            Text(
              member.active ? 'Aktif' : 'Tidak Aktif',
              style: TextStyle(
                color: member.active ? const Color(0xff10b981) : Colors.red,
                fontWeight: FontWeight.bold,
              ),
            ),
            const Spacer(),
            Text(
              'Berlaku sampai ${fd(member.expiredAt)}',
              style: const TextStyle(color: muted, fontSize: 12),
            ),
          ],
        ),
      ],
    ),
  );
}

class Quick extends StatelessWidget {
  final IconData icon;
  final String text;
  final VoidCallback tap;
  const Quick(this.icon, this.text, this.tap, {super.key});
  @override
  Widget build(BuildContext c) => InkWell(
    onTap: tap,
    child: SizedBox(
      width: 68,
      child: Column(
        children: [
          CircleAvatar(
            backgroundColor: Colors.white,
            child: Icon(icon, color: ink),
          ),
          const SizedBox(height: 7),
          Text(
            text,
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 10,
              color: muted,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    ),
  );
}

class MembershipScreen extends StatelessWidget {
  final Member member;
  const MembershipScreen({super.key, required this.member});
  @override
  Widget build(BuildContext c) => Scaffold(
    appBar: AppBar(title: const Text('Kartu Membership')),
    body: ListView(
      padding: const EdgeInsets.all(16),
      children: [
        MembershipCard(member: member, onTap: () {}),
        const SizedBox(height: 24),
        DetailCard(
          rows: {
            'Nama': member.name,
            'Member Code': member.code,
            'Status': member.active ? 'Aktif' : 'Tidak Aktif',
            'Berlaku Sampai': fd(member.expiredAt),
          },
        ),
        const SizedBox(height: 16),
        FilledButton.tonalIcon(
          onPressed: () => open(c, MembershipStatusScreen(member: member)),
          icon: const Icon(Icons.verified_outlined),
          label: const Text('Lihat Status Membership'),
        ),
      ],
    ),
  );
}

class MembershipStatusScreen extends StatelessWidget {
  final Member member;
  const MembershipStatusScreen({super.key, required this.member});
  @override
  Widget build(BuildContext c) => Scaffold(
    appBar: AppBar(title: const Text('Status Membership')),
    body: ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              children: [
                CircleAvatar(
                  radius: 34,
                  backgroundColor: member.active
                      ? const Color(0xffe3f8ed)
                      : const Color(0xffffe2e6),
                  child: Icon(
                    member.active ? Icons.check_circle : Icons.error_outline,
                    color: member.active ? Colors.green : crimson,
                    size: 38,
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  member.active ? 'Membership Aktif' : 'Membership Tidak Aktif',
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),
        DetailCard(
          rows: {
            'Member Code': member.code,
            'Status': member.active ? 'Aktif' : 'Tidak Aktif',
            'Terakhir digunakan': fd(member.lastUsed),
            'Berlaku sampai': fd(member.expiredAt),
          },
        ),
      ],
    ),
  );
}

class BalanceScreen extends StatelessWidget {
  final Member member;
  const BalanceScreen({super.key, required this.member});
  @override
  Widget build(BuildContext c) => Scaffold(
    appBar: AppBar(title: const Text('Saldo Deposit')),
    body: Padding(
      padding: const EdgeInsets.all(16),
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(28),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(
                Icons.account_balance_wallet_outlined,
                color: crimson,
                size: 38,
              ),
              const SizedBox(height: 24),
              const Text('Saldo tersedia', style: TextStyle(color: muted)),
              const SizedBox(height: 8),
              Text(
                rupiah.format(member.balance),
                style: const TextStyle(
                  fontSize: 32,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 18),
              const Text(
                'Top up saldo hanya dapat dilakukan melalui Admin/Kasir Technolife.',
                style: TextStyle(color: muted, height: 1.5),
              ),
            ],
          ),
        ),
      ),
    ),
  );
}

class HistoryScreen extends StatefulWidget {
  const HistoryScreen({super.key});
  @override
  State<HistoryScreen> createState() => _HistoryState();
}

class _HistoryState extends State<HistoryScreen> {
  int tab = 0;
  @override
  void initState() {
    super.initState();
    final appState = context.read<AppState>(); // Ambil context di sini
    Future.microtask(() => appState.loadHistory());
  }

  @override
  Widget build(BuildContext c) {
    final s = c.watch<AppState>();
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Riwayat',
          style: TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: SegmentedButton<int>(
              segments: const [
                ButtonSegment(
                  value: 0,
                  label: Text('Transaksi'),
                  icon: Icon(Icons.receipt_long),
                ),
                ButtonSegment(
                  value: 1,
                  label: Text('Akses'),
                  icon: Icon(Icons.meeting_room_outlined),
                ),
              ],
              selected: {tab},
              onSelectionChanged: (v) => setState(() => tab = v.first),
            ),
          ),
          Expanded(
            child: s.error != null
                ? ErrorState(message: s.error!, retry: s.loadHistory)
                : tab == 0
                ? TransactionList(items: s.transactions)
                : AccessList(items: s.accesses),
          ),
        ],
      ),
    );
  }
}

class TransactionListScreen extends StatefulWidget {
  const TransactionListScreen({super.key});
  @override
  State<TransactionListScreen> createState() => _TxScreenState();
}

class _TxScreenState extends State<TransactionListScreen> {
  @override
  void initState() {
    super.initState();
    final appState = context.read<AppState>();
    Future.microtask(() => appState.loadHistory());
  }

  @override
  Widget build(BuildContext c) => Scaffold(
    appBar: AppBar(title: const Text('Riwayat Transaksi')),
    body: TransactionList(items: c.watch<AppState>().transactions),
  );
}

class TransactionList extends StatelessWidget {
  final List<Tx> items;
  const TransactionList({super.key, required this.items});
  @override
  Widget build(BuildContext c) {
    if (items.isEmpty) {
      return const EmptyState(
        icon: Icons.receipt_long_outlined,
        text: 'Belum ada transaksi',
      );
    }
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: items.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (_, i) {
        final t = items[i], plus = t.type == 'top_up';
        return Card(
          child: ListTile(
            onTap: () => open(c, TransactionDetailScreen(item: t)),
            leading: CircleAvatar(
              backgroundColor: plus
                  ? const Color(0xffe5f8ee)
                  : const Color(0xffffe5eb),
              child: Icon(
                plus ? Icons.add : Icons.meeting_room_outlined,
                color: plus ? Colors.green : crimson,
              ),
            ),
            title: Text(
              plus ? 'Top Up' : 'Room Access',
              style: const TextStyle(fontWeight: FontWeight.w700),
            ),
            subtitle: Text(
              '${fd(t.date)}${t.room == null ? '' : ' • ${t.room}'}',
            ),
            trailing: Text(
              '${plus ? '+' : '-'} ${rupiah.format(t.amount)}',
              style: TextStyle(
                color: plus ? Colors.green : crimson,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
        );
      },
    );
  }
}

class TransactionDetailScreen extends StatelessWidget {
  final Tx item;
  const TransactionDetailScreen({super.key, required this.item});
  @override
  Widget build(BuildContext c) => Scaffold(
    appBar: AppBar(title: const Text('Detail Transaksi')),
    body: ListView(
      padding: const EdgeInsets.all(16),
      children: [
        InfoCard(
          title: item.type == 'top_up' ? 'Top Up' : 'Room Access',
          value: rupiah.format(item.amount),
        ),
        const SizedBox(height: 16),
        DetailCard(
          rows: {
            'Transaction ID': '#${item.id}',
            'Jenis transaksi': item.type,
            'Nominal': rupiah.format(item.amount),
            'Room': item.room ?? '-',
            'Tanggal': fd(item.date),
            'Saldo sebelum': rupiah.format(item.before),
            'Saldo sesudah': rupiah.format(item.after),
            'Status': item.status,
          },
        ),
      ],
    ),
  );
}

class AccessHistoryScreen extends StatefulWidget {
  const AccessHistoryScreen({super.key});
  @override
  State<AccessHistoryScreen> createState() => _AccessState();
}

class _AccessState extends State<AccessHistoryScreen> {
  @override
  void initState() {
    super.initState();
    final appState = context.read<AppState>();
    Future.microtask(() => appState.loadHistory());
  }

  @override
  Widget build(BuildContext c) => Scaffold(
    appBar: AppBar(title: const Text('Riwayat Akses')),
    body: AccessList(items: c.watch<AppState>().accesses),
  );
}

class AccessList extends StatelessWidget {
  final List<AccessItem> items;
  const AccessList({super.key, required this.items});
  @override
  Widget build(BuildContext c) {
    if (items.isEmpty) {
      return const EmptyState(
        icon: Icons.meeting_room_outlined,
        text: 'Belum ada riwayat akses',
      );
    }
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: items.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (_, i) {
        final a = items[i], ok = a.status == 'success';
        return Card(
          child: ListTile(
            onTap: () => open(c, AccessDetailScreen(item: a)),
            leading: CircleAvatar(
              backgroundColor: ok
                  ? const Color(0xffe5f8ee)
                  : const Color(0xffffe5eb),
              child: Icon(
                ok ? Icons.check : Icons.close,
                color: ok ? Colors.green : crimson,
              ),
            ),
            title: Text(
              a.room,
              style: const TextStyle(fontWeight: FontWeight.w700),
            ),
            subtitle: Text('${fd(a.date)} • ${a.reason}'),
            trailing: Text(
              ok ? 'Berhasil' : 'Ditolak',
              style: TextStyle(
                color: ok ? Colors.green : crimson,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        );
      },
    );
  }
}

class AccessDetailScreen extends StatelessWidget {
  final AccessItem item;
  const AccessDetailScreen({super.key, required this.item});
  @override
  Widget build(BuildContext c) => Scaffold(
    appBar: AppBar(title: const Text('Detail Akses')),
    body: Padding(
      padding: const EdgeInsets.all(16),
      child: DetailCard(
        rows: {
          'Room': item.room,
          'UID': item.uid,
          'Status': item.status == 'success' ? 'Berhasil' : 'Ditolak',
          'Alasan': item.reason,
          'Waktu scan': fd(item.date),
        },
      ),
    ),
  );
}

class PromoScreen extends StatefulWidget {
  const PromoScreen({super.key});
  @override
  State<PromoScreen> createState() => _PromoState();
}

class _PromoState extends State<PromoScreen> {
  @override
  void initState() {
    super.initState();
    final appState = context.read<AppState>();
    Future.microtask(() => appState.loadPromos());
  }

  @override
  Widget build(BuildContext c) {
    final s = c.watch<AppState>();
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Promo & Penawaran',
          style: TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
      body: s.error != null
          ? ErrorState(message: s.error!, retry: s.loadPromos)
          : s.promos.isEmpty
          ? const EmptyState(
              icon: Icons.local_offer_outlined,
              text: 'Belum ada promo aktif',
            )
          : RefreshIndicator(
              onRefresh: s.loadPromos,
              child: ListView.separated(
                padding: const EdgeInsets.all(16),
                itemCount: s.promos.length,
                separatorBuilder: (_, __) => const SizedBox(height: 16),
                itemBuilder: (_, i) => PromoCard(
                  promo: s.promos[i],
                  onTap: () => open(c, PromoDetailScreen(promo: s.promos[i])),
                ),
              ),
            ),
    );
  }
}

class PromoCard extends StatelessWidget {
  final Promo promo;
  final VoidCallback onTap;
  const PromoCard({super.key, required this.promo, required this.onTap});
  @override
  Widget build(BuildContext context) => Card(
    clipBehavior: Clip.antiAlias,
    child: InkWell(
      onTap: onTap,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            height: 130,
            width: double.infinity,
            color: const Color(0xffeee9e9),
            child: promo.imageUrl == null
                ? const Icon(Icons.image_outlined, size: 52, color: muted)
                : Image.network(
                    promo.imageUrl!,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) =>
                        const Icon(Icons.broken_image_outlined, size: 48),
                  ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  promo.title,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  promo.description,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(color: muted),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    const Icon(
                      Icons.calendar_today_outlined,
                      size: 16,
                      color: muted,
                    ),
                    const SizedBox(width: 7),
                    Text(
                      'Berlaku s/d ${fd(promo.end)}',
                      style: const TextStyle(color: muted, fontSize: 12),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    ),
  );
}

class PromoDetailScreen extends StatelessWidget {
  final Promo promo;
  const PromoDetailScreen({super.key, required this.promo});
  @override
  Widget build(BuildContext c) => Scaffold(
    appBar: AppBar(title: const Text('Detail Promo')),
    body: ListView(
      children: [
        Container(
          height: 250,
          color: const Color(0xffeee9e9),
          child: promo.imageUrl == null
              ? const Icon(Icons.local_offer_outlined, size: 72, color: muted)
              : Image.network(
                  promo.imageUrl!,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) =>
                      const Icon(Icons.broken_image_outlined, size: 60),
                ),
        ),
        Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                promo.title,
                style: const TextStyle(
                  fontSize: 26,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 14),
              Text(
                '${fd(promo.start)} – ${fd(promo.end)}',
                style: const TextStyle(
                  color: crimson,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 24),
              Text(promo.description, style: const TextStyle(height: 1.6)),
              const SizedBox(height: 28),
              const Text(
                'Syarat & Ketentuan',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
              ),
              const SizedBox(height: 10),
              Text(
                promo.terms.isEmpty
                    ? 'Tidak ada syarat tambahan.'
                    : promo.terms,
                style: const TextStyle(color: muted, height: 1.6),
              ),
              const SizedBox(height: 24),
              Chip(
                label: Text(
                  promo.status == 'active' ? 'Promo Aktif' : 'Tidak Aktif',
                ),
                avatar: const Icon(Icons.circle, size: 10, color: Colors.green),
              ),
            ],
          ),
        ),
      ],
    ),
  );
}

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});
  @override
  Widget build(BuildContext c) {
    final s = c.watch<AppState>(), m = s.member;
    if (m == null) {
      return const SkeletonPage();
    }
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Profil',
          style: TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 34,
                    backgroundColor: const Color(0xffffe5eb),
                    child: Text(
                      m.name.isEmpty ? 'M' : m.name[0],
                      style: const TextStyle(
                        color: crimson,
                        fontSize: 26,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          m.name,
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        Text(m.code, style: const TextStyle(color: muted)),
                        Text(
                          m.active ? 'Aktif' : 'Tidak Aktif',
                          style: TextStyle(
                            color: m.active ? Colors.green : crimson,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          DetailCard(
            rows: {
              'Email': m.email.isEmpty ? '-' : m.email,
              'No. Handphone': m.phone,
              'Saldo Deposit': rupiah.format(m.balance),
              'Berlaku Sampai': fd(m.expiredAt),
              'Terakhir Digunakan': fd(m.lastUsed),
            },
          ),
          const SizedBox(height: 16),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.edit_outlined),
                  title: const Text('Edit Profil'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => open(c, EditProfileScreen(member: m)),
                ),
                const Divider(height: 1),
                const ListTile(
                  leading: Icon(Icons.security_outlined),
                  title: Text('Keamanan'),
                  subtitle: Text('Login aman dengan OTP'),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.logout, color: crimson),
                  title: const Text('Logout', style: TextStyle(color: crimson)),
                  onTap: () => showDialog(
                    context: c,
                    builder: (d) => AlertDialog(
                      title: const Text('Logout?'),
                      content: const Text('Anda akan keluar dari aplikasi.'),
                      actions: [
                        TextButton(
                          onPressed: () => Navigator.pop(d),
                          child: const Text('Batal'),
                        ),
                        FilledButton(
                          onPressed: () {
                            Navigator.pop(d);
                            s.logout();
                          },
                          child: const Text('Logout'),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class EditProfileScreen extends StatefulWidget {
  final Member member;
  const EditProfileScreen({super.key, required this.member});
  @override
  State<EditProfileScreen> createState() => _EditState();
}

class _EditState extends State<EditProfileScreen> {
  late final name = TextEditingController(text: widget.member.name),
      email = TextEditingController(text: widget.member.email),
      phone = TextEditingController(text: widget.member.phone);

  Future<void> save() async {
    try {
      await context.read<AppState>().updateProfile(
        name.text,
        email.text,
        phone.text,
      );
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Profil berhasil diperbarui.')),
      );
      Navigator.pop(context);
    } catch (_) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(context.read<AppState>().error ?? 'Gagal')),
      );
    }
  }

  @override
  Widget build(BuildContext c) => Scaffold(
    appBar: AppBar(title: const Text('Edit Profil')),
    body: ListView(
      padding: const EdgeInsets.all(16),
      children: [
        TextField(
          controller: name,
          decoration: const InputDecoration(
            labelText: 'Nama lengkap',
            border: OutlineInputBorder(),
          ),
        ),
        const SizedBox(height: 16),
        TextField(
          controller: email,
          keyboardType: TextInputType.emailAddress,
          decoration: const InputDecoration(
            labelText: 'Email',
            border: OutlineInputBorder(),
          ),
        ),
        const SizedBox(height: 16),
        TextField(
          controller: phone,
          keyboardType: TextInputType.phone,
          decoration: const InputDecoration(
            labelText: 'No. Handphone',
            border: OutlineInputBorder(),
          ),
        ),
        const SizedBox(height: 16),
        TextField(
          enabled: false,
          controller: TextEditingController(text: widget.member.code),
          decoration: const InputDecoration(
            labelText: 'Member Code (tidak dapat diubah)',
            border: OutlineInputBorder(),
          ),
        ),
        const SizedBox(height: 24),
        FilledButton(
          onPressed: c.watch<AppState>().busy ? null : save,
          style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(52)),
          child: const Text('Simpan Perubahan'),
        ),
      ],
    ),
  );
}

class InfoCard extends StatelessWidget {
  final String title, value;
  final String? action;
  final VoidCallback? onTap;
  const InfoCard({
    super.key,
    required this.title,
    required this.value,
    this.action,
    this.onTap,
  });
  @override
  Widget build(BuildContext c) => Card(
    child: InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Text(title, style: const TextStyle(color: muted)),
                const Spacer(),
                if (action != null)
                  Text(
                    action!,
                    style: const TextStyle(
                      color: crimson,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 18),
            Text(
              value,
              style: const TextStyle(fontSize: 27, fontWeight: FontWeight.w800),
            ),
          ],
        ),
      ),
    ),
  );
}

class DetailCard extends StatelessWidget {
  final Map<String, String> rows;
  const DetailCard({super.key, required this.rows});
  @override
  Widget build(BuildContext c) => Card(
    child: Padding(
      padding: const EdgeInsets.all(18),
      child: Column(
        children: rows.entries
            .map(
              (e) => Padding(
                padding: const EdgeInsets.symmetric(vertical: 10),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Text(e.key, style: const TextStyle(color: muted)),
                    ),
                    Expanded(
                      child: Text(
                        e.value,
                        textAlign: TextAlign.right,
                        style: const TextStyle(fontWeight: FontWeight.w700),
                      ),
                    ),
                  ],
                ),
              ),
            )
            .toList(),
      ),
    ),
  );
}

class EmptyState extends StatelessWidget {
  final IconData icon;
  final String text;
  const EmptyState({super.key, required this.icon, required this.text});
  @override
  Widget build(BuildContext c) => Center(
    child: Padding(
      padding: const EdgeInsets.all(40),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 58, color: Colors.grey.shade400),
          const SizedBox(height: 16),
          Text(
            text,
            style: const TextStyle(color: muted, fontWeight: FontWeight.w600),
          ),
        ],
      ),
    ),
  );
}

class ErrorState extends StatelessWidget {
  final String message;
  final Future<void> Function() retry;
  const ErrorState({super.key, required this.message, required this.retry});
  @override
  Widget build(BuildContext c) => Center(
    child: Padding(
      padding: const EdgeInsets.all(32),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.cloud_off_outlined, size: 58, color: crimson),
          const SizedBox(height: 16),
          Text(message, textAlign: TextAlign.center),
          const SizedBox(height: 18),
          OutlinedButton.icon(
            onPressed: retry,
            icon: const Icon(Icons.refresh),
            label: const Text('Coba Lagi'),
          ),
        ],
      ),
    ),
  );
}

class SkeletonPage extends StatelessWidget {
  const SkeletonPage({super.key});
  @override
  Widget build(BuildContext c) => Scaffold(
    body: SafeArea(
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: List.generate(
          4,
          (i) => Container(
            height: i == 1 ? 210 : 90,
            margin: const EdgeInsets.only(bottom: 16),
            decoration: BoxDecoration(
              color: Colors.grey.shade200,
              borderRadius: BorderRadius.circular(16),
            ),
          ),
        ),
      ),
    ),
  );
}
