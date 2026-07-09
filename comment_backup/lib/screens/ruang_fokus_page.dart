import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../models/focus_model.dart';
import '../services/focus_service.dart';
import '../utils/app_constants.dart';
import '../utils/responsive_utils.dart';
import '../widgets/fokus/timer_ring.dart';
import '../widgets/fokus/plant_visual.dart';
import '../widgets/fokus/focus_stats_card.dart';
import '../widgets/gara_app_bar.dart';

enum _TimerMode { focus, shortBreak, longBreak }

class RuangFokusPage extends StatefulWidget {
  const RuangFokusPage({super.key});

  @override
  State<RuangFokusPage> createState() => _RuangFokusPageState();
}

class _RuangFokusPageState extends State<RuangFokusPage> {
  FocusData _data = FocusData();

  _TimerMode _mode = _TimerMode.focus;
  int _secondsLeft = 25 * 60;
  bool _running = false;
  bool _paused = false;
  Timer? _timer;

  int get _totalSeconds => switch (_mode) {
        _TimerMode.focus => _data.settings.focusDuration * 60,
        _TimerMode.shortBreak => _data.settings.shortBreak * 60,
        _TimerMode.longBreak => _data.settings.longBreak * 60,
      };

  String get _modeLabel => switch (_mode) {
        _TimerMode.focus => 'Fokus',
        _TimerMode.shortBreak => 'Istirahat Pendek',
        _TimerMode.longBreak => 'Istirahat Panjang',
      };

  @override
  void initState() {
    super.initState();
    _initData();
  }

  Future<void> _initData() async {
    final d = await FocusService.load();
    setState(() {
      _data = d;
      _secondsLeft = d.settings.focusDuration * 60;
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  // ── Timer controls ────────────────────────────────────────
  void _start() {
    HapticFeedback.mediumImpact();
    setState(() { _running = true; _paused = false; });
    _timer = Timer.periodic(const Duration(seconds: 1), (_) => _tick());
  }

  void _pause() {
    _timer?.cancel();
    HapticFeedback.lightImpact();
    setState(() => _paused = true);
  }

  void _resume() {
    HapticFeedback.lightImpact();
    setState(() => _paused = false);
    _timer = Timer.periodic(const Duration(seconds: 1), (_) => _tick());
  }

  void _tick() {
    if (_paused) return;
    setState(() {
      if (_secondsLeft > 0) {
        _secondsLeft--;
      } else {
        _timer?.cancel();
        _onComplete();
      }
    });
  }

  void _onComplete() async {
    HapticFeedback.heavyImpact();
    if (_mode == _TimerMode.focus) {
      FocusService.completeSession(_data);
      await FocusService.save(_data);
      _showSnack('Sesi Fokus Selesai! Tanaman bertumbuh 🌱', Colors.green);
      // Switch ke istirahat
      final nextMode = _data.stats.totalSessions % 4 == 0
          ? _TimerMode.longBreak
          : _TimerMode.shortBreak;
      _setMode(nextMode);
    } else {
      _showSnack('Istirahat Selesai! Siap lanjut? 💪', const Color(0xFF3B82F6));
      _setMode(_TimerMode.focus);
    }
    setState(() { _running = false; _paused = false; });
  }

  void _reset() {
    _timer?.cancel();
    HapticFeedback.lightImpact();
    setState(() {
      _running = false;
      _paused = false;
      _secondsLeft = _totalSeconds;
    });
  }

  void _setMode(_TimerMode mode) {
    _timer?.cancel();
    setState(() {
      _mode = mode;
      _running = false;
      _paused = false;
      _secondsLeft = switch (mode) {
        _TimerMode.focus => _data.settings.focusDuration * 60,
        _TimerMode.shortBreak => _data.settings.shortBreak * 60,
        _TimerMode.longBreak => _data.settings.longBreak * 60,
      };
    });
  }

  void _showSettings() async {
    final result = await showDialog<Map<String, int>>(
      context: context,
      builder: (_) => _SettingsDialog(
        focus: _data.settings.focusDuration,
        shortBreak: _data.settings.shortBreak,
        longBreak: _data.settings.longBreak,
      ),
    );
    if (result == null) return;
    setState(() {
      _data.settings.focusDuration = result['focus']!;
      _data.settings.shortBreak = result['short']!;
      _data.settings.longBreak = result['long']!;
      if (!_running) _secondsLeft = _data.settings.focusDuration * 60;
    });
    await FocusService.save(_data);
  }

  void _showSnack(String msg, Color color) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg, style: GoogleFonts.poppins(fontSize: 13)),
      backgroundColor: color,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFDBEAFE),
      appBar: GaraAppBar(
        title: 'Ruang Fokus',
        titleIcon: Icons.center_focus_strong_rounded,
        iconColor: const Color(0xFF9333EA),
        iconBg: const Color(0xFFF3E8FF),
        actions: [
          IconButton(
            icon: const Icon(Icons.settings_rounded, size: 20),
            color: GaraColors.studentTextMuted,
            onPressed: _showSettings,
          ),
        ],
      ),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [Color(0xFFDBEAFE), Color(0xFF93C5FD)],
          ),
        ),
        child: Center(
          child: ConstrainedBox(
            constraints: BoxConstraints(
              maxWidth: GaraResponsive.contentMaxWidth(context),
            ),
            child: SingleChildScrollView(
              physics: const BouncingScrollPhysics(),
              padding: EdgeInsets.symmetric(
                horizontal: GaraResponsive.hPad(context),
              ),
              child: Column(children: [
                const SizedBox(height: 16),
                _buildStreakBar(),
                const SizedBox(height: 24),
                TimerRing(
                  secondsRemaining: _secondsLeft,
                  totalSeconds: _totalSeconds,
                  modeLabel: _modeLabel,
                ),
                const SizedBox(height: 8),
                Text('Sesi Hari Ini: ${_data.todaySessions}/4',
                    style: GoogleFonts.poppins(fontSize: 13, color: const Color(0xFF3B82F6))),
                const SizedBox(height: 20),
                _buildControls(),
                const SizedBox(height: 24),
                PlantVisual(level: _data.plantLevel),
                const SizedBox(height: 24),
                FocusStatsCard(data: _data),
                const SizedBox(height: 32),
              ]),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildStreakBar() => Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          _statBadge('🔥', '${_data.streak.current}', 'Streak Hari', Colors.orange),
          const SizedBox(width: 32),
          _statBadge('🌱', '${_data.plantLevel}', 'Level Tanaman', Colors.green),
        ],
      );

  Widget _statBadge(String emoji, String value, String label, Color color) => Column(children: [
        Row(mainAxisSize: MainAxisSize.min, children: [
          Text(emoji, style: const TextStyle(fontSize: 22)),
          const SizedBox(width: 4),
          Text(value,
              style: GoogleFonts.poppins(
                  fontSize: 28, fontWeight: FontWeight.w700, color: color)),
        ]),
        Text(label,
            style: GoogleFonts.poppins(fontSize: 11.5, color: const Color(0xFF1D4ED8))),
      ]);

  Widget _buildControls() => Wrap(
        spacing: 10,
        runSpacing: 10,
        alignment: WrapAlignment.center,
        children: [
          if (!_running)
            _btn(Icons.play_arrow_rounded, 'Mulai', const Color(0xFF2563EB), _start),
          if (_running && !_paused)
            _btn(Icons.pause_rounded, 'Jeda', const Color(0xFFF59E0B), _pause),
          if (_running && _paused)
            _btn(Icons.play_arrow_rounded, 'Lanjut', const Color(0xFF3B82F6), _resume),
          _btn(Icons.refresh_rounded, 'Reset', const Color(0xFF60A5FA), _reset),
        ],
      );

  Widget _btn(IconData icon, String label, Color color, VoidCallback onTap) =>
      ElevatedButton.icon(
        onPressed: onTap,
        icon: Icon(icon, size: 18),
        label: Text(label, style: GoogleFonts.poppins(fontWeight: FontWeight.w600, fontSize: 13)),
        style: ElevatedButton.styleFrom(
          backgroundColor: color,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          elevation: 0,
        ),
      );
}

// ── Settings Dialog ───────────────────────────────────────────
class _SettingsDialog extends StatefulWidget {
  final int focus;
  final int shortBreak;
  final int longBreak;

  const _SettingsDialog({
    required this.focus,
    required this.shortBreak,
    required this.longBreak,
  });

  @override
  State<_SettingsDialog> createState() => _SettingsDialogState();
}

class _SettingsDialogState extends State<_SettingsDialog> {
  late int _focus, _short, _long;

  @override
  void initState() {
    super.initState();
    _focus = widget.focus;
    _short = widget.shortBreak;
    _long = widget.longBreak;
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      title: Text('Pengaturan Timer',
          style: GoogleFonts.poppins(fontWeight: FontWeight.w700)),
      content: Column(mainAxisSize: MainAxisSize.min, children: [
        _spinRow('Durasi Fokus (menit)', _focus, 1, 60,
            (v) => setState(() => _focus = v)),
        const SizedBox(height: 12),
        _spinRow('Istirahat Pendek (menit)', _short, 1, 30,
            (v) => setState(() => _short = v)),
        const SizedBox(height: 12),
        _spinRow('Istirahat Panjang (menit)', _long, 1, 60,
            (v) => setState(() => _long = v)),
      ]),
      actions: [
        TextButton(onPressed: () => Navigator.pop(context),
            child: Text('Batal', style: GoogleFonts.poppins())),
        ElevatedButton(
          onPressed: () => Navigator.pop(context, {'focus': _focus, 'short': _short, 'long': _long}),
          style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF2563EB),
              foregroundColor: Colors.white, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
          child: Text('Simpan', style: GoogleFonts.poppins(fontWeight: FontWeight.w600)),
        ),
      ],
    );
  }

  Widget _spinRow(String label, int value, int min, int max, ValueChanged<int> onChange) =>
      Row(children: [
        Expanded(
          child: Text(label,
              style: GoogleFonts.poppins(fontSize: 13, color: const Color(0xFF1E3A8A))),
        ),
        IconButton(
          icon: const Icon(Icons.remove_circle_outline_rounded, size: 22),
          color: const Color(0xFF3B82F6),
          onPressed: value > min ? () => onChange(value - 1) : null,
          padding: EdgeInsets.zero, constraints: const BoxConstraints(),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8),
          child: Text('$value',
              style: GoogleFonts.poppins(fontSize: 15, fontWeight: FontWeight.w700,
                  color: const Color(0xFF1E3A8A))),
        ),
        IconButton(
          icon: const Icon(Icons.add_circle_outline_rounded, size: 22),
          color: const Color(0xFF3B82F6),
          onPressed: value < max ? () => onChange(value + 1) : null,
          padding: EdgeInsets.zero, constraints: const BoxConstraints(),
        ),
      ]);
}
