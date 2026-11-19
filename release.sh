#!/bin/bash
set -e

REPO="ibnusna/appsgara"
APK_SOURCE="build/app/outputs/flutter-apk/app-release.apk"
TEMP_DIR="/tmp/gara_release_$$"

# Cek apakah file APK hasil build ada
if [ ! -f "$APK_SOURCE" ]; then
  echo "❌ File APK tidak ditemukan di $APK_SOURCE. Jalankan 'flutter build apk --release' terlebih dahulu."
  exit 1
fi

# Cek apakah GitHub CLI (gh) sudah terpasang dan login
if ! command -v gh &> /dev/null; then
  echo "❌ GitHub CLI (gh) belum terinstall. Install dulu dengan: sudo apt install gh"
  exit 1
fi

# Ambil versi otomatis dari pubspec.yaml (misal: version: 1.5.3+1 -> v1.5.3)
PUBSPEC_VER=$(grep '^version:' pubspec.yaml | awk '{print $2}' | cut -d'+' -f1)
DEFAULT_TAG="v${PUBSPEC_VER:-1.5.3}"

echo "📦 File ditemukan: $APK_SOURCE ($(du -h "$APK_SOURCE" | cut -f1))"
read -p "🏷️  Masukkan Tag Rilis [Default: $DEFAULT_TAG]: " INPUT_TAG
TAG="${INPUT_TAG:-$DEFAULT_TAG}"

read -p "📝 Catatan Pembaruan Singkat: " NOTES
NOTES="${NOTES:-Pembaruan Gara Mobile $TAG}"

# Siapkan kedua nama file agar website downloader & fallback lama tetap kompatibel
mkdir -p "$TEMP_DIR"
cp "$APK_SOURCE" "$TEMP_DIR/Gara_Mobile.apk"
cp "$APK_SOURCE" "$TEMP_DIR/app-release.apk"

echo "🚀 Mengunggah rilis $TAG ke $REPO..."
gh release create "$TAG" \
  "$TEMP_DIR/Gara_Mobile.apk" \
  "$TEMP_DIR/app-release.apk" \
  --repo "$REPO" \
  --title "Gara Mobile $TAG" \
  --notes "$NOTES" \
  --latest

# Bersihkan file sementara
rm -rf "$TEMP_DIR"

echo "✅ Selesai! Rilis terbaru Gara Mobile $TAG sudah live di:"
echo "   https://github.com/$REPO/releases/tag/$TAG"
