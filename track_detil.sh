#!/bin/bash

# ==========================================
# BASELINE DATA 3 HARI LALU (DARI CHAT LOG)
# ==========================================
EXT_WRITE_3DAYS_AGO=63468
EXT_READ_3DAYS_AGO=98820

echo "=================================================="
echo "    📊 LAPORAN MUTASI & TRAFFIC DATA SSD IBNU     "
echo "=================================================="

# 1. PARSING SSD INTERNAL (NVMe nvme0n1)
echo -e "\n[1] METRIK SSD INTERNAL (NVMe)"
if [ -e /dev/nvme0n1 ]; then
    # Ambil raw data units (1 unit = 512000 bytes)
    nvme_write_raw=$(sudo nvme smart-log /dev/nvme0n1 | grep 'data_units_written' | awk '{print $3}' | tr -d ',')
    nvme_read_raw=$(sudo nvme smart-log /dev/nvme0n1 | grep 'data_units_read' | awk '{print $3}' | tr -d ',')
    
    # Hitung ke Gigabyte & Terabyte
    nvme_write_gb=$(echo "scale=2; ($nvme_write_raw * 512000) / (1024^3)" | bc)
    nvme_read_gb=$(echo "scale=2; ($nvme_read_raw * 512000) / (1024^3)" | bc)
    nvme_write_tb=$(echo "scale=4; $nvme_write_gb / 1024" | bc)
    nvme_read_tb=$(echo "scale=4; $nvme_read_gb / 1024" | bc)

    echo "• Total Ditulis (Lifetime) : $nvme_write_gb GB ($nvme_write_tb TB)"
    echo "• Total Dibaca (Lifetime)  : $nvme_read_gb GB ($nvme_read_tb TB)"
    echo "⚠️ Catatan Internal: Data 3 hari lalu kepotong grep sistem lu, angka hari ini otomatis jadi baseline baru."
else
    echo "❌ SSD Internal tidak terdeteksi."
fi

echo "--------------------------------------------------"

# 2. PARSING SSD EKSTERNAL (SATA sda)
echo -e "[2] METRIK SSD EKSTERNAL (V-GEN USB)"
if [ -e /dev/sda ]; then
    # Ambil raw value attribute 241 dan 242 (Skala vendor Phison: 1 count = 32MB)
    ext_write_raw=$(sudo smartctl -d sat -A /dev/sda | grep 'Total_LBAs_Written' | awk '{print $10}')
    ext_read_raw=$(sudo smartctl -d sat -A /dev/sda | grep 'Total_LBAs_Read' | awk '{print $10}')
    
    # Konversi Lifetime ke GB (Raw * 32 / 1024)
    ext_write_lifetime_gb=$(echo "scale=2; ($ext_write_raw * 32) / 1024" | bc)
    ext_read_lifetime_gb=$(echo "scale=2; ($ext_read_raw * 32) / 1024" | bc)

    # HITUNG DELTA MUTASI 3 HARI TERAKHIR
    diff_write_raw=$((ext_write_raw - EXT_WRITE_3DAYS_AGO))
    diff_read_raw=$((ext_read_raw - EXT_READ_3DAYS_AGO))
    
    diff_write_gb=$(echo "scale=2; ($diff_write_raw * 32) / 1024" | bc)
    diff_read_gb=$(echo "scale=2; ($diff_read_raw * 32) / 1024" | bc)
    diff_write_tb=$(echo "scale=4; $diff_write_gb / 1024" | bc)
    diff_read_tb=$(echo "scale=4; $diff_read_gb / 1024" | bc)

    echo "• Total Ditulis (Lifetime) : $ext_write_lifetime_gb GB"
    echo "• Total Dibaca (Lifetime)  : $ext_read_lifetime_gb GB"
    echo -e "\n🔥 REAL DATA: MUTASI 3 HARI TERAKHIR (SWAP + PENGGUNAAN)"
    echo "• Data Baru Tertulis (3 Hari) : $diff_write_gb GB ($diff_write_tb TBW)"
    echo "• Data Baru Dibaca (3 Hari)   : $diff_read_gb GB ($diff_read_tb TB)"
else
    echo "❌ SSD Eksternal tidak terdeteksi. Colok dulu USB-nya."
fi
echo "=================================================="
