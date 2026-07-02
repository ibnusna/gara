// Model untuk satu catatan — versi 2 dengan dukungan rich-blocks & kategori
// STORAGE: JSON di shared_preferences key 'garuda_akademi_ruang_catatan'
import 'dart:convert';
import 'package:flutter/material.dart';

// ── Jenis kategori catatan ─────────────────────────────────────
enum NoteCategory {
  umum,       // 📝 Umum
  target,     // 🎯 Target Belajar
  tugas,      // 📋 Daftar Tugas
  draft,      // ✏️  Draft Tugas
  pokokNote,  // 📌 Catatan Tugas Pokok
}

extension NoteCategoryExt on NoteCategory {
  String get label {
    switch (this) {
      case NoteCategory.umum:       return 'Umum';
      case NoteCategory.target:     return 'Target Belajar';
      case NoteCategory.tugas:      return 'Daftar Tugas';
      case NoteCategory.draft:      return 'Draft Tugas';
      case NoteCategory.pokokNote:  return 'Catatan Pokok';
    }
  }

  IconData get icon {
    switch (this) {
      case NoteCategory.umum:       return Icons.notes_rounded;
      case NoteCategory.target:     return Icons.track_changes_rounded;
      case NoteCategory.tugas:      return Icons.assignment_rounded;
      case NoteCategory.draft:      return Icons.edit_document;
      case NoteCategory.pokokNote:  return Icons.push_pin_rounded;
    }
  }
}

// ── Jenis blok konten dalam catatan ──────────────────────────
enum BlockType {
  text,         // teks biasa
  bold,         // **teks tebal**
  strikethrough,// ~~coret~~
  bullet,       // • poin
  numbered,     // 1. 2. 3.
  checklist,    // ☐ / ☑
  dateEntry,    // 📅 tanggal
}

class NoteBlock {
  BlockType type;
  String content;
  bool checked; // hanya untuk checklist

  NoteBlock({
    required this.type,
    this.content = '',
    this.checked = false,
  });

  factory NoteBlock.fromJson(Map<String, dynamic> j) => NoteBlock(
        type: BlockType.values.firstWhere(
          (e) => e.name == (j['type'] as String),
          orElse: () => BlockType.text,
        ),
        content: j['content'] as String? ?? '',
        checked: j['checked'] as bool? ?? false,
      );

  Map<String, dynamic> toJson() => {
        'type': type.name,
        'content': content,
        'checked': checked,
      };
}

// ── Model utama catatan ────────────────────────────────────────
class NoteModel {
  final String id;
  String title;
  List<NoteBlock> blocks;
  NoteCategory category;
  final DateTime createdAt;
  DateTime updatedAt;

  NoteModel({
    required this.id,
    required this.title,
    required this.blocks,
    required this.createdAt,
    required this.updatedAt,
    this.category = NoteCategory.umum,
  });

  /// Preview teks plain untuk ditampilkan di card
  String get plainPreview {
    final sb = StringBuffer();
    for (final b in blocks) {
      if (sb.length > 120) break;
      if (b.content.isNotEmpty) sb.write('${b.content} ');
    }
    final s = sb.toString().trim();
    return s.length > 110 ? '${s.substring(0, 110)}…' : s;
  }

  /// Konversi lama: content string → blocks (migrasi data lama)
  static List<NoteBlock> contentToBlocks(String content) {
    if (content.isEmpty) return [];
    return content
        .split('\n')
        .map((line) => NoteBlock(type: BlockType.text, content: line))
        .toList();
  }

  factory NoteModel.create({
    String title = 'Catatan Tanpa Judul',
    NoteCategory category = NoteCategory.umum,
    List<NoteBlock>? blocks,
  }) {
    final now = DateTime.now();
    return NoteModel(
      id: '${now.millisecondsSinceEpoch}',
      title: title,
      blocks: blocks ?? [],
      category: category,
      createdAt: now,
      updatedAt: now,
    );
  }

  factory NoteModel.fromJson(Map<String, dynamic> j) {
    // Migrasi data lama yang tidak punya 'blocks'
    List<NoteBlock> blocks;
    if (j['blocks'] != null) {
      blocks = (j['blocks'] as List)
          .map((b) => NoteBlock.fromJson(b as Map<String, dynamic>))
          .toList();
    } else {
      blocks = contentToBlocks(j['content'] as String? ?? '');
    }

    return NoteModel(
      id: j['id'] as String,
      title: j['title'] as String,
      blocks: blocks,
      category: NoteCategory.values.firstWhere(
        (e) => e.name == (j['category'] as String?),
        orElse: () => NoteCategory.umum,
      ),
      createdAt: DateTime.parse(j['created_at'] as String),
      updatedAt: DateTime.parse(j['updated_at'] as String),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'title': title,
        'blocks': blocks.map((b) => b.toJson()).toList(),
        'category': category.name,
        'created_at': createdAt.toIso8601String(),
        'updated_at': updatedAt.toIso8601String(),
      };

  static String encodeList(List<NoteModel> notes) =>
      jsonEncode(notes.map((n) => n.toJson()).toList());

  static List<NoteModel> decodeList(String raw) {
    final list = jsonDecode(raw) as List;
    return list.map((j) => NoteModel.fromJson(j as Map<String, dynamic>)).toList();
  }
}
