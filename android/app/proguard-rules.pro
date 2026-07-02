# ============================================================
#  GARA Flutter — ProGuard Rules
#  Diperlukan saat minifyEnabled = true pada release build
# ============================================================

# ── Flutter Engine ────────────────────────────────────────────
-keep class io.flutter.** { *; }
-keep class io.flutter.embedding.** { *; }
-keep class io.flutter.plugin.** { *; }
-dontwarn io.flutter.**

# ── flutter_inappwebview ──────────────────────────────────────
-keep class com.pichillilorenzo.flutter_inappwebview.** { *; }
-dontwarn com.pichillilorenzo.**

# ── flutter_windowmanager ─────────────────────────────────────
-keep class com.baitouwei.flutter_windowmanager.** { *; }

# ── Kotlin coroutines ─────────────────────────────────────────
-keep class kotlinx.coroutines.** { *; }
-dontwarn kotlinx.coroutines.**

# ── OkHttp (digunakan flutter HTTP client) ───────────────────
-dontwarn okhttp3.**
-dontwarn okio.**
-keep class okhttp3.** { *; }
-keep interface okhttp3.** { *; }

# ── Jangan strip kelas yang diakses via reflection ────────────
-keepattributes *Annotation*
-keepattributes SourceFile,LineNumberTable

# ── Google Fonts (network fonts) ─────────────────────────────
-keep class com.google.** { *; }
-dontwarn com.google.**
