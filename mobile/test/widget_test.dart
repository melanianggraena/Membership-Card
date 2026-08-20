import 'package:flutter_test/flutter_test.dart';
import 'package:technolife_membership/main.dart';
import 'package:provider/provider.dart';
import 'package:technolife_membership/core/api_client.dart';
import 'package:technolife_membership/providers/app_state.dart';

void main() {
  testWidgets('App dapat dirender', (tester) async {
    await tester.pumpWidget(ChangeNotifierProvider(create: (_) => AppState(ApiClient()), child: const TechnolifeApp()));
    expect(find.text('Technolife'), findsOneWidget);
  });
}
