import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../utils/app_constants.dart';
import '../../services/notification_service.dart';

class NotifikasiTab extends StatefulWidget {
  const NotifikasiTab({super.key});

  @override
  State<NotifikasiTab> createState() => _NotifikasiTabState();
}

class _NotifikasiTabState extends State<NotifikasiTab> {
  List<dynamic> _notifs = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  Future<void> _fetchData() async {
    setState(() => _isLoading = true);
    final data = await NotificationService.fetchNotifications();
    if (mounted) {
      setState(() {
        _notifs = data;
        _isLoading = false;
      });
    }
  }

  Future<void> _handleRefresh() async {
    final data = await NotificationService.fetchNotifications();
    if (mounted) {
      setState(() {
        _notifs = data;
      });
    }
  }

  Future<void> _markAsRead(int index, int id) async {
    if (_notifs[index]['is_read'] == 1 || _notifs[index]['is_read'] == true) return;

    // Optimistic UI update
    setState(() {
      _notifs[index]['is_read'] = 1;
    });

    final success = await NotificationService.markAsRead(id);
    if (!success && mounted) {
      // Revert if failed
      setState(() {
        _notifs[index]['is_read'] = 0;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Gagal menandai notifikasi dibaca.')),
      );
    }
  }

  int get _unreadCount {
    return _notifs.where((n) => n['is_read'] == 0 || n['is_read'] == false).length;
  }

  @override
  Widget build(BuildContext context) {
    return Column(children: [
      _buildBar(),
      const Divider(color: GaraColors.studentBorder, height: 1),
      Expanded(
        child: _isLoading ? _buildSkeleton() : _buildList(),
      ),
    ]);
  }

  Widget _buildBar() {
    return Container(
      color: GaraColors.studentSurface,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(children: [
        Text(
          _isLoading ? 'Memuat Notifikasi...' : (_unreadCount > 0 ? '$_unreadCount Belum Dibaca' : 'Semua Sudah Dibaca'),
          style: GoogleFonts.poppins(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: _unreadCount > 0 ? GaraColors.studentPrimary : GaraColors.studentTextMuted),
        ),
      ]),
    );
  }

  Widget _buildSkeleton() {
    return ListView.separated(
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(vertical: 8),
      itemCount: 5,
      separatorBuilder: (_, __) => Container(
          height: 1, margin: const EdgeInsets.only(left: 72),
          color: GaraColors.studentBorder),
      itemBuilder: (_, i) => Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Container(
            width: 44, height: 44,
            decoration: BoxDecoration(color: Colors.grey.shade200, shape: BoxShape.circle),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Container(width: 150, height: 14, color: Colors.grey.shade200),
            const SizedBox(height: 8),
            Container(width: double.infinity, height: 12, color: Colors.grey.shade200),
            const SizedBox(height: 4),
            Container(width: 100, height: 12, color: Colors.grey.shade200),
          ])),
        ]),
      ),
    );
  }

  Widget _buildList() {
    if (_notifs.isEmpty) {
      return Center(
        child: Text('Belum ada notifikasi.',
          style: GoogleFonts.poppins(color: GaraColors.studentTextMuted)),
      );
    }
    return RefreshIndicator(
      onRefresh: _handleRefresh,
      color: GaraColors.studentPrimary,
      child: ListView.separated(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.symmetric(vertical: 8),
        itemCount: _notifs.length,
        separatorBuilder: (_, __) => Container(
            height: 1, margin: const EdgeInsets.only(left: 72),
            color: GaraColors.studentBorder),
        itemBuilder: (_, i) {
          final item = _notifs[i];
          final bool isRead = item['is_read'] == 1 || item['is_read'] == true;
          return _NotifTile(
            item: item,
            isRead: isRead,
            onTap: () => _markAsRead(i, item['id']),
          );
        },
      ),
    );
  }
}

class _NotifTile extends StatelessWidget {
  final dynamic item;
  final bool isRead;
  final VoidCallback onTap;
  
  const _NotifTile({required this.item, required this.isRead, required this.onTap});

  @override
  Widget build(BuildContext context) {
    // Tentukan icon dan warna berdasarkan tipe
    IconData icon = Icons.notifications;
    Color iconColor = Colors.grey;
    Color iconBg = Colors.grey.shade100;

    final type = (item['type'] ?? '').toString().toLowerCase();
    if (type == 'tugas') {
      icon = Icons.description_rounded;
      iconColor = Colors.amber.shade700;
      iconBg = Colors.amber.shade50;
    } else if (type == 'ujian') {
      icon = Icons.campaign_rounded;
      iconColor = Colors.red.shade600;
      iconBg = Colors.red.shade50;
    } else if (type == 'diskusi') {
      icon = Icons.chat_rounded;
      iconColor = Colors.blue.shade600;
      iconBg = Colors.blue.shade50;
    } else if (type == 'info') {
      icon = Icons.info_rounded;
      iconColor = Colors.green.shade600;
      iconBg = Colors.green.shade50;
    }

    final dateStr = item['created_at'] != null ? item['created_at'].toString() : '';

    return InkWell(
      onTap: onTap,
      child: Container(
        color: isRead ? Colors.white : const Color(0xFFEFF6FF), // Blue shaded if unread
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          // Icon
          Container(
            width: 44, height: 44,
            decoration: BoxDecoration(color: iconBg, shape: BoxShape.circle),
            child: Icon(icon, color: iconColor, size: 20),
          ),
          const SizedBox(width: 12),
          // Teks
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [
              Expanded(
                child: Text(item['title'] ?? 'Notifikasi',
                    style: GoogleFonts.poppins(
                        fontSize: 13,
                        fontWeight: isRead ? FontWeight.w500 : FontWeight.w700,
                        color: GaraColors.studentTextMain)),
              ),
              if (!isRead)
                Container(width: 8, height: 8,
                    decoration: const BoxDecoration(color: GaraColors.studentPrimary, shape: BoxShape.circle)),
            ]),
            const SizedBox(height: 3),
            Text(item['body'] ?? '',
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: GoogleFonts.poppins(fontSize: 12, color: GaraColors.studentTextMuted, height: 1.4)),
            const SizedBox(height: 4),
            Text(dateStr,
                style: GoogleFonts.poppins(fontSize: 11, color: GaraColors.studentPrimary, fontWeight: FontWeight.w500)),
          ])),
        ]),
      ),
    );
  }
}
