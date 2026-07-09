






















import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../models/trivia_model.dart';
import '../../utils/app_constants.dart';
import 'ds_glass_card.dart';

class TriviaWidget extends StatefulWidget {
  const TriviaWidget({super.key});

  @override
  State<TriviaWidget> createState() => _TriviaWidgetState();
}

class _TriviaWidgetState extends State<TriviaWidget> {
  int _index = 0;
  int? _selected;
  bool _answered = false;

  TriviaQuestion get _current => triviaBank[_index % triviaBank.length];

  void _answer(int i) {
    if (_answered) return;
    HapticFeedback.lightImpact();
    setState(() {
      _selected = i;
      _answered = true;
    });
    Future.delayed(const Duration(milliseconds: 1500), () {
      if (mounted) {
        setState(() {
          _index++;
          _selected = null;
          _answered = false;
        });
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final q = _current;
    
    
    return GaraGlassCard(
      borderRadius: BorderRadius.circular(24),
      padding: const EdgeInsets.all(16),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        
        Row(children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: const Color(0xFFFFFBEB),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.psychology_rounded,
                color: Color(0xFFD97706), size: 16),
          ),
          const SizedBox(width: 8),
          Text(
            'Brain Warmup',
            style: GoogleFonts.plusJakartaSans(
              fontWeight: FontWeight.w700,
              fontSize: 13.5,
              color: GaraColors.dsSlate800,
            ),
          ),
          const Spacer(),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.60),
              borderRadius: BorderRadius.circular(999),
              border: Border.all(color: GaraColors.dsSlate200),
            ),
            child: Text(
              'Science & Comp',
              style: GoogleFonts.plusJakartaSans(
                fontSize: 10,
                fontWeight: FontWeight.w600,
                color: GaraColors.dsSlate500,
              ),
            ),
          ),
        ]),

        const SizedBox(height: 10),
        const Divider(color: GaraColors.dsSlate200, height: 1),
        const SizedBox(height: 14),

        
        Text(
          q.pertanyaan,
          style: GoogleFonts.plusJakartaSans(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: GaraColors.dsSlate800,
            height: 1.5,
          ),
        ),
        const SizedBox(height: 14),

        
        Column(
          children: List.generate(q.pilihan.length, (i) {
            Color btnColor = Colors.white.withOpacity(0.50);
            Color borderColor = GaraColors.dsSlate200;
            Color textColor = GaraColors.dsSlate800;
            Widget? trailing;

            if (_answered) {
              if (i == q.jawabanBenar) {
                btnColor = const Color(0xFFDCFCE7);
                borderColor = const Color(0xFF22C55E);
                textColor = const Color(0xFF14532D);
                trailing = const Icon(Icons.check_circle_rounded,
                    color: Color(0xFF16A34A), size: 16);
              } else if (i == _selected) {
                btnColor = const Color(0xFFFEE2E2);
                borderColor = const Color(0xFFEF4444);
                textColor = const Color(0xFF7F1D1D);
                trailing = const Icon(Icons.cancel_rounded,
                    color: Color(0xFFEF4444), size: 16);
              }
            }

            return GestureDetector(
              onTap: () => _answer(i),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                width: double.infinity,
                margin: const EdgeInsets.only(bottom: 8),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
                decoration: BoxDecoration(
                  color: btnColor,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: borderColor),
                ),
                child: Row(children: [
                  _label(String.fromCharCode(65 + i), borderColor, textColor),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      q.pilihan[i],
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 12.5,
                        color: textColor,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                  if (trailing != null) trailing,
                ]),
              ),
            );
          }),
        ),
      ]),
    );
  }

  Widget _label(String char, Color border, Color text) {
    return Container(
      width: 22,
      height: 22,
      decoration: BoxDecoration(
        color: border.withOpacity(0.15),
        shape: BoxShape.circle,
        border: Border.all(color: border),
      ),
      child: Center(
        child: Text(
          char,
          style: GoogleFonts.plusJakartaSans(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: text,
          ),
        ),
      ),
    );
  }
}
