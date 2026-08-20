import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'core/api_client.dart';
import 'providers/app_state.dart';
import 'screens/app_screens.dart';

void main() => runApp(
  ChangeNotifierProvider(
    create: (_) => AppState(ApiClient())..restore(),
    child: const TechnolifeApp(),
  ),
);

class TechnolifeApp extends StatelessWidget {
  const TechnolifeApp({super.key});
  @override
  Widget build(BuildContext context) => MaterialApp(
    title: 'Technolife Membership',
    debugShowCheckedModeBanner: false,
    theme: ThemeData(
      useMaterial3: true,
      scaffoldBackgroundColor: const Color(0xfff8f9fa),
      colorScheme: ColorScheme.fromSeed(
        seedColor: const Color(0xffdc143c),
        primary: const Color(0xffdc143c),
        surface: Colors.white,
      ),
      fontFamily: 'Arial',
      appBarTheme: const AppBarTheme(
        backgroundColor: Colors.white,
        foregroundColor: Color(0xffb1002c),
        elevation: 1,
        centerTitle: false,
      ),
      cardTheme: CardThemeData(
        color: Colors.white,
        elevation: 1,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: Color(0xffe5e7eb)),
        ),
      ),
    ),
    home: const RootScreen(),
  );
}
