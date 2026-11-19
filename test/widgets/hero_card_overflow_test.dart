import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:gara_flutter/widgets/dashboard/hero_card.dart';

void main() {
  testWidgets('HeroCard does not overflow on narrow 320dp width with 80-char long text', (tester) async {
    final errors = <FlutterErrorDetails>[];
    final originalOnError = FlutterError.onError;
    FlutterError.onError = (details) {
      errors.add(details);
      originalOnError?.call(details);
    };

    // Enforce small viewport: 320 x 600
    tester.view.physicalSize = const Size(320, 600);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(() {
      tester.view.resetPhysicalSize();
      FlutterError.onError = originalOnError;
    });

    const longSubject = 'Pendidikan Kewarganegaraan dan Budi Pekerti Serta Karakter Bangsa Tingkat Lanjutan';
    const longClass = 'XII-MIPA-Super-Unggulan-2026';

    await tester.pumpWidget(
      const MaterialApp(
        home: Scaffold(
          body: SingleChildScrollView(
            child: SizedBox(
              width: 320,
              child: HeroCard(
                selectedMapel: longSubject,
                kelasSiswa: longClass,
                namaSiswa: 'Siswa Berprestasi GARA',
              ),
            ),
          ),
        ),
      ),
    );

    await tester.pumpAndSettle();

    // Verify zero layout / RenderFlex overflow exceptions occurred
    expect(errors, isEmpty);
    expect(tester.takeException(), isNull);

    // Verify HeroCard rendered
    expect(find.byType(HeroCard), findsOneWidget);
    expect(find.text('Aktivitas Terakhir'), findsOneWidget);
    expect(find.text('Lihat Semua'), findsOneWidget);
  });
}
