import 'package:flutter/material.dart';
import '../services/update_service.dart';

class UpdatePage extends StatefulWidget {
  final bool isForceUpdate;
  final String releaseNotes;
  final String latestVersion;

  const UpdatePage({
    super.key,
    required this.isForceUpdate,
    required this.releaseNotes,
    required this.latestVersion,
  });

  @override
  State<UpdatePage> createState() => _UpdatePageState();
}

class _UpdatePageState extends State<UpdatePage> {
  final UpdateService _updateService = UpdateService();
  bool _isDownloading = false;
  double _progress = 0.0;
  String _statusMessage = "Menunggu...";

  Future<void> _startUpdate() async {
    setState(() {
      _isDownloading = true;
      _statusMessage = "Mencari tautan unduhan...";
    });

    final apkUrl = await _updateService.fetchLatestApkUrl();
    if (apkUrl == null) {
      setState(() {
        _isDownloading = false;
        _statusMessage = "Gagal menemukan tautan update.";
      });
      return;
    }

    setState(() {
      _statusMessage = "Mengunduh pembaruan...";
    });

    try {
      await _updateService.downloadAndInstallApk(apkUrl, (received, total) {
        if (total != -1) {
          setState(() {
            _progress = received / total;
          });
        }
      });
      setState(() {
        _statusMessage = "Unduhan selesai. Memulai pemasangan...";
      });
    } catch (e) {
      setState(() {
        _isDownloading = false;
        _statusMessage = "Gagal mengunduh: $e";
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Pembaruan Aplikasi"),
        automaticallyImplyLeading: !widget.isForceUpdate && !_isDownloading,
      ),
      body: PopScope(
        canPop: !widget.isForceUpdate && !_isDownloading,
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Icon(Icons.system_update, size: 80, color: Colors.green),
              const SizedBox(height: 24),
              Text(
                "Versi Terbaru Tersedia (${widget.latestVersion})",
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              Text(
                widget.releaseNotes,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 16),
              ),
              const SizedBox(height: 32),
              if (_isDownloading) ...[
                LinearProgressIndicator(value: _progress),
                const SizedBox(height: 16),
                Text(
                  _statusMessage,
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 14, color: Colors.grey),
                ),
                const SizedBox(height: 8),
                Text(
                  "${(_progress * 100).toStringAsFixed(1)}%",
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
              ] else ...[
                ElevatedButton(
                  onPressed: _startUpdate,
                  child: const Text("Perbarui Sekarang"),
                ),
                if (!widget.isForceUpdate) ...[
                  const SizedBox(height: 8),
                  TextButton(
                    onPressed: () => Navigator.of(context).pop(),
                    child: const Text("Nanti"),
                  ),
                ]
              ],
            ],
          ),
        ),
      ),
    );
  }
}
