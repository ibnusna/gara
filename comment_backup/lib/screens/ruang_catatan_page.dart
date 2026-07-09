// ============================================================
//  Ruang Catatan — Main Page  (iOS-style, animated)
//  v2: card-menu • kategori filter • animasi masuk ala iOS
// ============================================================
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../models/note_model.dart';
import '../services/notes_service.dart';
import '../utils/app_constants.dart';
import '../utils/responsive_utils.dart';
import '../widgets/gara_app_bar.dart';
import 'note_editor_page.dart';

class RuangCatatanPage extends StatefulWidget {
  const RuangCatatanPage({super.key});

  @override
  State<RuangCatatanPage> createState() => _RuangCatatanPageState();
}

class _RuangCatatanPageState extends State<RuangCatatanPage>
    with SingleTickerProviderStateMixin {
  List<NoteModel> _notes = [];
  String _search = '';
  bool _loading = true;
  NoteCategory? _filterCat; // null = semua

  late AnimationController _fadeCtrl;
  late Animation<double> _fadeAnim;

  @override
  void initState() {
    super.initState();
    _fadeCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 500));
    _fadeAnim = CurvedAnimation(parent: _fadeCtrl, curve: Curves.easeOut);
    _load();
  }

  @override
  void dispose() {
    _fadeCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    final data = await NotesService.load();
    if (!mounted) return;
    setState(() {
      _notes = data;
      _loading = false;
    });
    _fadeCtrl.forward(from: 0);
  }

  List<NoteModel> get _filtered {
    var list = _notes;
    if (_filterCat != null) {
      list = list.where((n) => n.category == _filterCat).toList();
    }
    if (_search.isNotEmpty) {
      final q = _search.toLowerCase();
      list = list
          .where((n) =>
              n.title.toLowerCase().contains(q) ||
              n.plainPreview.toLowerCase().contains(q))
          .toList();
    }
    return list;
  }

  // ── Buka editor catatan ───────────────────────────────────
  Future<void> _openNote({NoteModel? existing}) async {
    HapticFeedback.lightImpact();
    final note = existing ?? NoteModel.create();
    final result = await Navigator.push<List<NoteModel>>(
      context,
      _iosPageRoute(
        NoteEditorPage(note: note, allNotes: _notes),
      ),
    );
    if (result != null && mounted) {
      setState(() => _notes = result);
      _fadeCtrl.forward(from: 0);
    } else if (existing == null && mounted) {
      // Catatan baru mungkin sudah tersimpan via auto-save; reload
      _load();
    }
  }

  // ── Hapus catatan ─────────────────────────────────────────
  Future<void> _delete(NoteModel note) async {
    HapticFeedback.mediumImpact();
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: GaraColors.studentSurface,
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text('Hapus Catatan?',
            style: GoogleFonts.poppins(fontWeight: FontWeight.w700)),
        content: Text('Catatan "${note.title}" akan dihapus permanen.',
            style: GoogleFonts.poppins(fontSize: 13)),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: Text('Batal', style: GoogleFonts.poppins())),
          TextButton(
              onPressed: () => Navigator.pop(context, true),
              child: Text('Hapus',
                  style: GoogleFonts.poppins(color: GaraColors.danger))),
        ],
      ),
    );
    if (ok != true) return;
    final updated = await NotesService.delete(_notes, note.id);
    if (mounted) setState(() => _notes = updated);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: GaraColors.studentBgBody,
      appBar: _buildAppBar(),
      floatingActionButton: _buildFab(),
      body: _loading
          ? const Center(
              child: CircularProgressIndicator(color: GaraColors.studentPrimary))
          : FadeTransition(
              opacity: _fadeAnim,
              child: Center(
                child: ConstrainedBox(
                  constraints: BoxConstraints(
                    maxWidth: GaraResponsive.contentMaxWidth(context),
                  ),
                  child: Column(children: [
                    _buildHeaderStats(),
                    _buildSearchAndFilter(),
                    Expanded(
                      child: _filtered.isEmpty
                          ? _buildEmpty()
                          : _buildNoteList(),
                    ),
                  ]),
                ),
              ),
            ),
    );
  }

  // ── AppBar ────────────────────────────────────────────────
  GaraAppBar _buildAppBar() => GaraAppBar(
        title: 'Ruang Catatan',
        titleIcon: Icons.sticky_note_2_rounded,
        iconColor: const Color(0xFFD97706),
        iconBg: const Color(0xFFFEF3C7),
        actions: [
          IconButton(
            icon: const Icon(Icons.add_rounded, size: 22),
            color: GaraColors.studentPrimary,
            tooltip: 'Catatan Baru',
            onPressed: () => _openNote(),
          ),
        ],
      );

  // ── Stats header ──────────────────────────────────────────
  Widget _buildHeaderStats() {
    final total = _notes.length;
    final byCategory = <NoteCategory, int>{};
    for (final n in _notes) {
      byCategory[n.category] = (byCategory[n.category] ?? 0) + 1;
    }
    final hPad = GaraResponsive.hPad(context);
    return Container(
      margin: EdgeInsets.fromLTRB(hPad, 14, hPad, 0),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0B57D0), Color(0xFF1A73E8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
              color: GaraColors.studentPrimary.withOpacity(0.25),
              blurRadius: 16,
              offset: const Offset(0, 6)),
        ],
      ),
      child: Row(children: [
        // Jumlah catatan
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('Total Catatan',
                style: GoogleFonts.poppins(
                    fontSize: 11, color: Colors.white70)),
            Text('$total',
                style: GoogleFonts.poppins(
                    fontSize: 36,
                    fontWeight: FontWeight.w800,
                    color: Colors.white,
                    height: 1.1)),
            Text(total == 1 ? 'catatan' : 'catatan',
                style: GoogleFonts.poppins(
                    fontSize: 12, color: Colors.white70)),
          ]),
        ),
        // Mini-chart kategori
        Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: NoteCategory.values
              .where((c) => (byCategory[c] ?? 0) > 0)
              .take(4)
              .map((c) => Padding(
                    padding: const EdgeInsets.only(bottom: 3),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(c.icon, size: 14, color: Colors.white),
                        const SizedBox(width: 4),
                        Text('${byCategory[c]}',
                            style: GoogleFonts.poppins(
                                fontSize: 12,
                                color: Colors.white,
                                fontWeight: FontWeight.w600)),
                      ],
                    ),
                  ))
              .toList(),
        ),
      ]),
    );
  }

  // ── Search + Category filter ──────────────────────────────
  Widget _buildSearchAndFilter() {
    final hPad = GaraResponsive.hPad(context);
    return Padding(
      padding: EdgeInsets.fromLTRB(hPad, 12, hPad, 4),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        // Search bar
        TextField(
          onChanged: (v) => setState(() => _search = v),
          style: GoogleFonts.poppins(fontSize: 13.5),
          decoration: InputDecoration(
            hintText: 'Cari catatan…',
            hintStyle: GoogleFonts.poppins(
                color: GaraColors.studentTextMuted, fontSize: 13),
            prefixIcon: const Icon(Icons.search_rounded,
                color: GaraColors.studentPrimary, size: 20),
            filled: true,
            fillColor: GaraColors.studentSurface,
            contentPadding:
                const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide:
                    const BorderSide(color: GaraColors.studentBorder)),
            enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide:
                    const BorderSide(color: GaraColors.studentBorder)),
            focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(
                    color: GaraColors.studentPrimary, width: 1.5)),
          ),
        ),
        const SizedBox(height: 10),
        // Category chips
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          physics: const BouncingScrollPhysics(),
          child: Row(
            children: [
              _catChip(null, Icons.all_inbox_rounded, 'Semua'),
              ...NoteCategory.values
                  .map((c) => _catChip(c, c.icon, c.label)),
            ],
          ),
        ),
      ]),
    );
  }

  Widget _catChip(NoteCategory? cat, IconData icon, String label) {
    final selected = _filterCat == cat;
    return GestureDetector(
      onTap: () {
        HapticFeedback.selectionClick();
        setState(() => _filterCat = cat);
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        margin: const EdgeInsets.only(right: 8),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: selected
              ? GaraColors.studentPrimary
              : GaraColors.studentSurface,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color:
                selected ? GaraColors.studentPrimary : GaraColors.studentBorder,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 14, color: selected ? Colors.white : GaraColors.studentTextMuted),
            const SizedBox(width: 4),
            Text(
              label,
              style: GoogleFonts.poppins(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: selected ? Colors.white : GaraColors.studentTextMain),
            ),
          ],
        ),
      ),
    );
  }

  // ── Daftar catatan ────────────────────────────────────────
  Widget _buildNoteList() {
    final hPad = GaraResponsive.hPad(context);
    return ListView.builder(
      padding: EdgeInsets.fromLTRB(hPad, 8, hPad, 100),
      physics: const BouncingScrollPhysics(),
      itemCount: _filtered.length,
      itemBuilder: (_, i) {
        final note = _filtered[i];
        return _NoteCard(
          note: note,
          index: i,
          onTap: () => _openNote(existing: note),
          onDelete: () => _delete(note),
        );
      },
    );
  }

  // ── Empty state ───────────────────────────────────────────
  Widget _buildEmpty() => Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            Container(
              width: 90,
              height: 90,
              decoration: const BoxDecoration(
                  color: Color(0xFFFEF3C7), shape: BoxShape.circle),
              child: const Icon(Icons.sticky_note_2_rounded,
                  color: Color(0xFFD97706), size: 40),
            ),
            const SizedBox(height: 20),
            Text(
              _search.isNotEmpty || _filterCat != null
                  ? 'Catatan tidak ditemukan'
                  : 'Belum ada catatan',
              style: GoogleFonts.poppins(
                  fontSize: 17,
                  fontWeight: FontWeight.w700,
                  color: GaraColors.studentTextMain),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              _search.isNotEmpty || _filterCat != null
                  ? 'Coba kata kunci atau filter yang berbeda'
                  : 'Ketuk ✙ untuk membuat catatan pertamamu',
              style: GoogleFonts.poppins(
                  fontSize: 13,
                  color: GaraColors.studentTextMuted,
                  height: 1.5),
              textAlign: TextAlign.center,
            ),
          ]),
        ),
      );

  // ── FAB ───────────────────────────────────────────────────
  Widget _buildFab() => FloatingActionButton(
        onPressed: () => _openNote(),
        backgroundColor: GaraColors.studentPrimary,
        foregroundColor: Colors.white,
        shape: const CircleBorder(),
        tooltip: 'Catatan Baru',
        elevation: 4,
        child: const Icon(Icons.add_rounded, size: 28),
      );
}

// ── Note Card ──────────────────────────────────────────────────
class _NoteCard extends StatefulWidget {
  final NoteModel note;
  final int index;
  final VoidCallback onTap;
  final VoidCallback onDelete;

  const _NoteCard({
    required this.note,
    required this.index,
    required this.onTap,
    required this.onDelete,
  });

  @override
  State<_NoteCard> createState() => _NoteCardState();
}

class _NoteCardState extends State<_NoteCard>
    with SingleTickerProviderStateMixin {
  late AnimationController _ctrl;
  late Animation<double> _scaleAnim;
  late Animation<Offset> _slideAnim;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
        vsync: this,
        duration: Duration(milliseconds: 300 + widget.index * 40));
    _scaleAnim =
        Tween<double>(begin: 0.93, end: 1.0).animate(
          CurvedAnimation(parent: _ctrl, curve: Curves.easeOutCubic));
    _slideAnim = Tween<Offset>(
            begin: const Offset(0, 0.05), end: Offset.zero)
        .animate(CurvedAnimation(parent: _ctrl, curve: Curves.easeOutCubic));
    _ctrl.forward();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  String _relativeTime(DateTime dt) {
    final diff = DateTime.now().difference(dt);
    if (diff.inMinutes < 1) return 'Baru saja';
    if (diff.inMinutes < 60) return '${diff.inMinutes} mnt lalu';
    if (diff.inHours < 24) return '${diff.inHours} jam lalu';
    if (diff.inDays < 7) return '${diff.inDays} hr lalu';
    const m = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
    return '${dt.day} ${m[dt.month - 1]}';
  }

  @override
  Widget build(BuildContext context) {
    final note = widget.note;
    return SlideTransition(
      position: _slideAnim,
      child: ScaleTransition(
        scale: _scaleAnim,
        child: GestureDetector(
          onLongPress: () {
            HapticFeedback.mediumImpact();
            _showContextMenu(context);
          },
          child: Container(
            margin: const EdgeInsets.only(bottom: 10),
            decoration: BoxDecoration(
              color: GaraColors.studentSurface,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: GaraColors.studentBorder),
              boxShadow: [
                BoxShadow(
                    color: Colors.black.withOpacity(0.04),
                    blurRadius: 8,
                    offset: const Offset(0, 2)),
              ],
            ),
            child: Material(
              color: Colors.transparent,
              child: InkWell(
                onTap: () {
                  HapticFeedback.lightImpact();
                  widget.onTap();
                },
                borderRadius: BorderRadius.circular(18),
                child: Padding(
                  padding: const EdgeInsets.all(15),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header: kategori + waktu
                      Row(children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: GaraColors.studentPrimaryLight,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(note.category.icon, size: 12, color: GaraColors.studentPrimary),
                              const SizedBox(width: 4),
                              Text(
                                note.category.label,
                                style: GoogleFonts.poppins(
                                    fontSize: 10,
                                    color: GaraColors.studentPrimary,
                                    fontWeight: FontWeight.w600),
                              ),
                            ]
                          ),
                        ),
                        const Spacer(),
                        Text(
                          _relativeTime(note.updatedAt),
                          style: GoogleFonts.poppins(
                              fontSize: 11,
                              color: GaraColors.studentTextMuted),
                        ),
                      ]),
                      const SizedBox(height: 10),
                      // Judul
                      Text(
                        note.title,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.poppins(
                            fontSize: 15,
                            fontWeight: FontWeight.w700,
                            color: GaraColors.studentTextMain),
                      ),
                      // Preview
                      if (note.plainPreview.isNotEmpty) ...[
                        const SizedBox(height: 5),
                        Text(
                          note.plainPreview,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.poppins(
                              fontSize: 12.5,
                              color: GaraColors.studentTextMuted,
                              height: 1.45),
                        ),
                      ],
                      const SizedBox(height: 10),
                      // Footer: jumlah blok + ikon
                      Row(children: [
                        _blockBadge(note),
                        const Spacer(),
                        const Icon(Icons.chevron_right_rounded,
                            size: 18, color: GaraColors.studentBorder),
                      ]),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _blockBadge(NoteModel note) {
    final checkCount = note.blocks
        .where((b) => b.type == BlockType.checklist)
        .length;
    final checkedCount = note.blocks
        .where((b) => b.type == BlockType.checklist && b.checked)
        .length;

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(Icons.sticky_note_2_outlined,
            size: 12, color: GaraColors.studentTextMuted),
        const SizedBox(width: 3),
        Text('${note.blocks.length} blok',
            style: GoogleFonts.poppins(
                fontSize: 11, color: GaraColors.studentTextMuted)),
        if (checkCount > 0) ...[
          const SizedBox(width: 8),
          Icon(Icons.check_box_rounded,
              size: 12, color: const Color(0xFF22C55E)),
          const SizedBox(width: 3),
          Text('$checkedCount/$checkCount',
              style: GoogleFonts.poppins(
                  fontSize: 11, color: const Color(0xFF22C55E))),
        ],
      ],
    );
  }

  void _showContextMenu(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (_) => Container(
        margin: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: GaraColors.studentSurface,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(height: 12),
            Container(
              width: 36,
              height: 4,
              decoration: BoxDecoration(
                  color: GaraColors.studentBorder,
                  borderRadius: BorderRadius.circular(2)),
            ),
            const SizedBox(height: 8),
            ListTile(
              leading: const Icon(Icons.edit_rounded,
                  color: GaraColors.studentPrimary),
              title: Text('Edit Catatan',
                  style: GoogleFonts.poppins(
                      fontWeight: FontWeight.w600,
                      color: GaraColors.studentTextMain)),
              onTap: () {
                Navigator.pop(context);
                widget.onTap();
              },
            ),
            const Divider(height: 1, color: GaraColors.studentBorder),
            ListTile(
              leading:
                  const Icon(Icons.delete_rounded, color: GaraColors.danger),
              title: Text('Hapus',
                  style: GoogleFonts.poppins(
                      fontWeight: FontWeight.w600, color: GaraColors.danger)),
              onTap: () {
                Navigator.pop(context);
                widget.onDelete();
              },
            ),
            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }
}

// ── Transisi halaman ala iOS ───────────────────────────────────
PageRouteBuilder<T> _iosPageRoute<T>(Widget page) {
  return PageRouteBuilder<T>(
    pageBuilder: (ctx, animation, secondary) => page,
    transitionsBuilder: (ctx, animation, secondary, child) {
      return SlideTransition(
        position: Tween<Offset>(
          begin: const Offset(1.0, 0),
          end: Offset.zero,
        ).animate(CurvedAnimation(
            parent: animation, curve: Curves.easeInOutCubic)),
        child: child,
      );
    },
    transitionDuration: const Duration(milliseconds: 320),
    reverseTransitionDuration: const Duration(milliseconds: 280),
  );
}
