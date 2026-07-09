

import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../models/note_model.dart';
import '../services/notes_service.dart';
import '../utils/app_constants.dart';

class NoteEditorPage extends StatefulWidget {
  final NoteModel note;
  final List<NoteModel> allNotes;

  const NoteEditorPage({
    super.key,
    required this.note,
    required this.allNotes,
  });

  @override
  State<NoteEditorPage> createState() => _NoteEditorPageState();
}

class _NoteEditorPageState extends State<NoteEditorPage>
    with TickerProviderStateMixin {
  late NoteModel _note;
  late List<NoteModel> _allNotes;
  late final TextEditingController _titleCtrl;
  late AnimationController _saveIndicatorCtrl;
  Timer? _autoSaveTimer;
  bool _saving = false;
  bool _justSaved = false;

  // Block editor state
  final List<TextEditingController> _controllers = [];
  final List<FocusNode> _focusNodes = [];
  final ScrollController _scrollCtrl = ScrollController();

  @override
  void initState() {
    super.initState();
    _note = widget.note;
    _allNotes = List.from(widget.allNotes);
    _titleCtrl = TextEditingController(text: _note.title);
    _saveIndicatorCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 400),
    );

    // Inisialisasi controllers per block
    for (final block in _note.blocks) {
      _addController(block.content);
    }

    if (_note.blocks.isEmpty) {
      _addBlock(BlockType.text);
    }

    _titleCtrl.addListener(_scheduleAutoSave);
  }

  void _addController(String text) {
    final ctrl = TextEditingController(text: text);
    final focus = FocusNode();
    ctrl.addListener(_scheduleAutoSave);
    _controllers.add(ctrl);
    _focusNodes.add(focus);
  }

  @override
  void dispose() {
    _autoSaveTimer?.cancel();
    _titleCtrl.dispose();
    _saveIndicatorCtrl.dispose();
    // Dispose semua controllers dan focusNodes dengan aman
    for (final c in _controllers) {
      c.removeListener(_scheduleAutoSave);
      c.dispose();
    }
    for (final f in _focusNodes) {
      f.dispose();
    }
    _scrollCtrl.dispose();
    super.dispose();
  }

  // ── Auto-save ──────────────────────────────────────────────────────────────
  void _scheduleAutoSave() {
    _autoSaveTimer?.cancel();
    _autoSaveTimer = Timer(const Duration(milliseconds: 800), _doSave);
  }

  Future<void> _doSave() async {
    if (!mounted) return;
    setState(() => _saving = true);

    // Sinkronisasi blok dari controllers sebelum simpan
    _syncBlocksFromControllers();
    _note.title = _titleCtrl.text.trim().isEmpty
        ? 'Catatan Tanpa Judul'
        : _titleCtrl.text;

    _allNotes = await NotesService.saveOne(_allNotes, _note);

    if (!mounted) return;
    setState(() {
      _saving = false;
      _justSaved = true;
    });
    await Future.delayed(const Duration(seconds: 2));
    if (mounted) setState(() => _justSaved = false);
  }

  void _syncBlocksFromControllers() {
    // Bounds check: pastikan jumlah blocks dan controllers sinkron
    final syncLen =
        _note.blocks.length < _controllers.length
            ? _note.blocks.length
            : _controllers.length;
    for (int i = 0; i < syncLen; i++) {
      _note.blocks[i].content = _controllers[i].text;
    }
  }

  // ── Block management ───────────────────────────────────────────────────────
  void _addBlock(BlockType type, {String content = '', int? afterIndex}) {
    final block = NoteBlock(type: type, content: content);
    final idx = afterIndex != null ? afterIndex + 1 : _note.blocks.length;

    final ctrl = TextEditingController(text: content);
    final focus = FocusNode();
    ctrl.addListener(_scheduleAutoSave);

    setState(() {
      _note.blocks.insert(idx, block);
      _controllers.insert(idx, ctrl);
      _focusNodes.insert(idx, focus);
    });

    // Gunakan addPostFrameCallback agar tidak race condition dengan rebuild
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted && idx < _focusNodes.length) {
        _focusNodes[idx].requestFocus();
      }
    });
    _scheduleAutoSave();
  }

  void _removeBlock(int idx) {
    if (_note.blocks.length <= 1) return; // minimal 1 block

    // Ambil referensi dulu sebelum setState
    final ctrlToDispose = _controllers[idx];
    final focusToDispose = _focusNodes[idx];
    final prevFocusIdx = idx > 0 ? idx - 1 : null;

    setState(() {
      _note.blocks.removeAt(idx);
      _controllers.removeAt(idx);
      _focusNodes.removeAt(idx);
    });

    // Dispose SETELAH setState selesai (via addPostFrameCallback)
    // untuk menghindari crash karena Flutter masih menggunakan widget
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ctrlToDispose.removeListener(_scheduleAutoSave);
      ctrlToDispose.dispose();
      focusToDispose.dispose();

      // Pindahkan focus ke block sebelumnya
      if (prevFocusIdx != null && mounted && prevFocusIdx < _focusNodes.length) {
        _focusNodes[prevFocusIdx].requestFocus();
      }
    });
    _scheduleAutoSave();
  }

  void _toggleCheck(int idx) {
    HapticFeedback.selectionClick();
    setState(() => _note.blocks[idx].checked = !_note.blocks[idx].checked);
    _scheduleAutoSave();
  }

  void _insertDate() {
    final now = DateTime.now();
    final formatted =
        '${now.day.toString().padLeft(2, '0')}/${now.month.toString().padLeft(2, '0')}/${now.year}';
    final activeIdx = _focusNodes.indexWhere((f) => f.hasFocus);
    final targetIdx = activeIdx >= 0 ? activeIdx : _note.blocks.length - 1;
    _addBlock(BlockType.dateEntry, content: '📅 $formatted', afterIndex: targetIdx);
  }

  // ── Build ──────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: GaraColors.studentSurface,
      // FIX: resizeToAvoidBottomInset agar keyboard tidak overlap konten
      resizeToAvoidBottomInset: true,
      appBar: _buildAppBar(),
      body: Column(
        children: [
          Expanded(child: _buildEditor()),
          _buildFormattingToolbar(),
        ],
      ),
    );
  }

  PreferredSizeWidget _buildAppBar() {
    return AppBar(
      backgroundColor: GaraColors.studentSurface,
      foregroundColor: GaraColors.studentTextMain,
      elevation: 0,
      surfaceTintColor: Colors.transparent,
      leading: _buildBackButton(),
      leadingWidth: 110,
      title: _buildSaveIndicator(),
      centerTitle: true,
      actions: [_buildCategoryChip(), const SizedBox(width: 8)],
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(1),
        child: Container(color: GaraColors.studentBorder, height: 0.5),
      ),
    );
  }

  Widget _buildBackButton() {
    return GestureDetector(
      onTap: () async {
        HapticFeedback.lightImpact();
        _autoSaveTimer?.cancel();
        await _doSave();
        if (mounted) Navigator.pop(context, _allNotes);
      },
      child: Container(
        margin: const EdgeInsets.only(left: 8),
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
        decoration: BoxDecoration(
          color: GaraColors.studentBgBody,
          borderRadius: BorderRadius.circular(10),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.chevron_left_rounded,
                size: 20, color: GaraColors.studentPrimary),
            Text('Kembali',
                style: GoogleFonts.poppins(
                    fontSize: 12,
                    color: GaraColors.studentPrimary,
                    fontWeight: FontWeight.w500)),
          ],
        ),
      ),
    );
  }

  Widget _buildSaveIndicator() {
    if (_saving) {
      return Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          SizedBox(
            width: 12,
            height: 12,
            child: CircularProgressIndicator(
              strokeWidth: 1.5,
              color: GaraColors.studentTextMuted,
            ),
          ),
          const SizedBox(width: 6),
          Text('Menyimpan…',
              style: GoogleFonts.poppins(
                  fontSize: 12, color: GaraColors.studentTextMuted)),
        ],
      );
    } else if (_justSaved) {
      return Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.check_circle_rounded,
              size: 14, color: Color(0xFF22C55E)),
          const SizedBox(width: 5),
          Text('Tersimpan',
              style: GoogleFonts.poppins(
                  fontSize: 12, color: const Color(0xFF22C55E))),
        ],
      );
    }
    return Text('Catatan',
        style: GoogleFonts.poppins(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: GaraColors.studentTextMain));
  }

  Widget _buildCategoryChip() {
    return GestureDetector(
      onTap: _showCategoryPicker,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: GaraColors.studentPrimaryLight,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(_note.category.icon, size: 14, color: GaraColors.studentPrimary),
            const SizedBox(width: 4),
            Text(
              _note.category.label,
              style: GoogleFonts.poppins(
                  fontSize: 11,
                  color: GaraColors.studentPrimary,
                  fontWeight: FontWeight.w600),
            ),
          ],
        ),
      ),
    );
  }

  // ── Editor ─────────────────────────────────────────────────────────────────
  Widget _buildEditor() {
    return ListView(
      controller: _scrollCtrl,
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 16),
      physics: const BouncingScrollPhysics(),
      children: [
        // Title field
        TextField(
          controller: _titleCtrl,
          style: GoogleFonts.poppins(
              fontSize: 22,
              fontWeight: FontWeight.w700,
              color: GaraColors.studentTextMain),
          decoration: InputDecoration(
            hintText: 'Judul catatan…',
            hintStyle: GoogleFonts.poppins(
                fontSize: 22,
                fontWeight: FontWeight.w700,
                color: GaraColors.studentBorder),
            border: InputBorder.none,
          ),
          keyboardType: TextInputType.text,
          textInputAction: TextInputAction.next,
          maxLines: null,
        ),

        Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: Text(
            _formatDate(_note.createdAt),
            style: GoogleFonts.poppins(
                fontSize: 11.5, color: GaraColors.studentTextMuted),
          ),
        ),
        const Divider(color: GaraColors.studentBorder, height: 1),
        const SizedBox(height: 12),

        // Block fields
        ..._buildBlockWidgets(),

        const SizedBox(height: 8),
        GestureDetector(
          onTap: () => _addBlock(BlockType.text),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: Row(
              children: [
                Icon(Icons.add_circle_outline_rounded,
                    size: 16, color: GaraColors.studentTextMuted),
                const SizedBox(width: 6),
                Text('Tambah baris baru…',
                    style: GoogleFonts.poppins(
                        fontSize: 13, color: GaraColors.studentTextMuted)),
              ],
            ),
          ),
        ),
        const SizedBox(height: 100),
      ],
    );
  }

  List<Widget> _buildBlockWidgets() {
    final widgets = <Widget>[];
    for (int i = 0; i < _note.blocks.length; i++) {
      // Guard: jangan render jika controllers belum sinkron
      if (i >= _controllers.length || i >= _focusNodes.length) break;
      widgets.add(_buildBlockRow(i));
    }
    return widgets;
  }

  Widget _buildBlockRow(int idx) {
    final block = _note.blocks[idx];
    final ctrl = _controllers[idx];
    final focus = _focusNodes[idx];

    int numberIndex = 1;
    if (block.type == BlockType.numbered) {
      for (int i = 0; i < idx; i++) {
        if (_note.blocks[i].type == BlockType.numbered) {
          numberIndex++;
        }
      }
    }

    Widget? prefix;
    TextStyle textStyle = GoogleFonts.poppins(
        fontSize: 14.5, color: GaraColors.studentTextMain, height: 1.6);

    switch (block.type) {
      case BlockType.bold:
        textStyle = textStyle.copyWith(fontWeight: FontWeight.w700);
        break;
      case BlockType.strikethrough:
        textStyle = textStyle.copyWith(
            decoration: TextDecoration.lineThrough,
            color: GaraColors.studentTextMuted);
        break;
      case BlockType.bullet:
        prefix = Text('•',
            style: GoogleFonts.poppins(
                fontSize: 16, color: GaraColors.studentPrimary, height: 1.5));
        break;
      case BlockType.numbered:
        prefix = Text('$numberIndex.',
            style: GoogleFonts.poppins(
                fontSize: 14.5,
                color: GaraColors.studentPrimary,
                fontWeight: FontWeight.w600,
                height: 1.6));
        break;
      case BlockType.checklist:
        prefix = GestureDetector(
          onTap: () => _toggleCheck(idx),
          child: AnimatedSwitcher(
            duration: const Duration(milliseconds: 200),
            child: block.checked
                ? const Icon(Icons.check_circle_rounded,
                    key: ValueKey('checked'), size: 20, color: Color(0xFF22C55E))
                : Icon(Icons.radio_button_unchecked_rounded,
                    key: const ValueKey('unchecked'),
                    size: 20,
                    color: GaraColors.studentTextMuted),
          ),
        );
        if (block.checked) {
          textStyle = textStyle.copyWith(
              decoration: TextDecoration.lineThrough,
              color: GaraColors.studentTextMuted);
        }
        break;
      case BlockType.dateEntry:
        textStyle = textStyle.copyWith(
            color: GaraColors.studentPrimary, fontWeight: FontWeight.w500);
        prefix = const Icon(Icons.calendar_today_rounded,
            size: 16, color: GaraColors.studentPrimary);
        break;
      default:
        break;
    }

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (prefix != null) ...[
            SizedBox(
                width: 24,
                child: Align(alignment: Alignment.topRight, child: prefix)),
            const SizedBox(width: 8),
          ],
          Expanded(
            child: TextField(
              controller: ctrl,
              focusNode: focus,
              style: textStyle,
              decoration: InputDecoration(
                hintText: block.type == BlockType.checklist
                    ? 'Item checklist…'
                    : block.type == BlockType.bullet
                        ? 'Poin baru…'
                        : block.type == BlockType.numbered
                            ? 'Item baru…'
                            : 'Ketik di sini…',
                hintStyle: GoogleFonts.poppins(
                    fontSize: 14, color: GaraColors.studentBorder),
                border: InputBorder.none,
                isDense: true,
                contentPadding: EdgeInsets.zero,
              ),
              maxLines: null,
              keyboardType: TextInputType.multiline,
              textInputAction: TextInputAction.newline,
              onSubmitted: (_) => _addBlock(block.type, afterIndex: idx),
            ),
          ),
        ],
      ),
    );
  }

  // ── Toolbar ────────────────────────────────────────────────────────────────
  Widget _buildFormattingToolbar() {
    return Container(
      decoration: const BoxDecoration(
        color: GaraColors.studentSurface,
        border: Border(top: BorderSide(color: GaraColors.studentBorder)),
      ),
      child: SafeArea(
        top: false,
        child: SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          physics: const BouncingScrollPhysics(),
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          child: Row(
            children: [
              _toolbarBtn(
                icon: Icons.format_bold_rounded,
                label: 'Bold',
                onTap: () => _addBlock(BlockType.bold),
              ),
              _toolbarBtn(
                icon: Icons.format_strikethrough_rounded,
                label: 'Coret',
                onTap: () => _addBlock(BlockType.strikethrough),
              ),
              _toolbarDivider(),
              _toolbarBtn(
                icon: Icons.format_list_bulleted_rounded,
                label: 'Poin',
                onTap: () => _addBlock(BlockType.bullet),
              ),
              _toolbarBtn(
                icon: Icons.format_list_numbered_rounded,
                label: 'Nomor',
                onTap: () => _addBlock(BlockType.numbered),
              ),
              _toolbarBtn(
                icon: Icons.check_box_rounded,
                label: 'Checklist',
                onTap: () => _addBlock(BlockType.checklist),
              ),
              _toolbarDivider(),
              _toolbarBtn(
                icon: Icons.calendar_month_rounded,
                label: 'Tanggal',
                onTap: _insertDate,
              ),
              _toolbarBtn(
                icon: Icons.text_fields_rounded,
                label: 'Teks',
                onTap: () => _addBlock(BlockType.text),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _toolbarBtn({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return Padding(
      padding: const EdgeInsets.only(right: 4),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () {
            HapticFeedback.selectionClick();
            onTap();
          },
          borderRadius: BorderRadius.circular(10),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(
              color: GaraColors.studentBgBody,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(icon, size: 18, color: GaraColors.studentPrimary),
                const SizedBox(height: 2),
                Text(label,
                    style: GoogleFonts.poppins(
                        fontSize: 9.5,
                        color: GaraColors.studentTextMuted,
                        fontWeight: FontWeight.w500)),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _toolbarDivider() => Container(
        width: 1,
        height: 32,
        color: GaraColors.studentBorder,
        margin: const EdgeInsets.symmetric(horizontal: 6),
      );

  // ── Category picker ────────────────────────────────────────────────────────
  void _showCategoryPicker() {
    HapticFeedback.mediumImpact();
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (_) => _CategoryPickerSheet(
        selected: _note.category,
        onSelect: (cat) {
          setState(() => _note.category = cat);
          _scheduleAutoSave();
          Navigator.pop(context);
        },
      ),
    );
  }

  String _formatDate(DateTime dt) {
    const months = [
      'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
      'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    return '${dt.day} ${months[dt.month - 1]} ${dt.year}  •  '
        '${dt.hour.toString().padLeft(2, '0')}:'
        '${dt.minute.toString().padLeft(2, '0')}';
  }
}


class _CategoryPickerSheet extends StatelessWidget {
  final NoteCategory selected;
  final ValueChanged<NoteCategory> onSelect;

  const _CategoryPickerSheet({
    required this.selected,
    required this.onSelect,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: GaraColors.studentSurface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 36, height: 4,
              decoration: BoxDecoration(
                color: GaraColors.studentBorder,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Text('Pilih Kategori',
              style: GoogleFonts.poppins(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: GaraColors.studentTextMain)),
          const SizedBox(height: 14),
          ...NoteCategory.values.map((cat) => _buildCategoryItem(cat)),
        ],
      ),
    );
  }

  Widget _buildCategoryItem(NoteCategory cat) {
    final isSelected = cat == selected;
    return AnimatedContainer(
      duration: const Duration(milliseconds: 150),
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: isSelected
            ? GaraColors.studentPrimaryLight
            : GaraColors.studentBgBody,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color:
              isSelected ? GaraColors.studentPrimary : GaraColors.studentBorder,
          width: isSelected ? 1.5 : 1,
        ),
      ),
      child: ListTile(
        onTap: () => onSelect(cat),
        leading: Icon(cat.icon,
            color: isSelected ? GaraColors.studentPrimary : GaraColors.studentTextMuted),
        title: Text(cat.label,
            style: GoogleFonts.poppins(
                fontSize: 14,
                fontWeight:
                    isSelected ? FontWeight.w700 : FontWeight.w500,
                color: isSelected
                    ? GaraColors.studentPrimary
                    : GaraColors.studentTextMain)),
        trailing: isSelected
            ? const Icon(Icons.check_circle_rounded,
                color: GaraColors.studentPrimary, size: 20)
            : null,
        dense: true,
      ),
    );
  }
}
