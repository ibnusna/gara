#!/bin/bash
echo "=================================================="
echo "    📊 LAPORAN MUTASI & TRAFFIC DATA SSD IBNU     "
echo "=================================================="

# 1. PARSING SSD INTERNAL (NVMe nvme0n1) - FIXED VERSION
echo -e "\n[1] METRIK SSD INTERNAL (NVMe)"
if [ -e /dev/nvme0n1 ]; then
    # Ambil raw data dengan grep case-insensitive dan bersihkan spasi/koma
    nvme_write_raw=$(sudo nvme smart-log /dev/nvme0n1 | grep -i 'data_units_written' | awk -F':' '{print $2}' | tr -d '[:space:],')
    nvme_read_raw=$(sudo nvme smart-log /dev/nvme0n1 | grep -i 'data_units_read' | awk -F':' '{print $2}' | tr -d '[:space:],')
    
    # Validasi jika data tidak kosong
    if [ -n "$nvme_write_raw" ] && [ "$nvme_write_raw" -eq "$nvme_write_raw" ] 2>/dev/null; then
        # Konversi: 1 unit NVMe = 512.000 bytes. Ke GB = (units * 512000) / 1024^3
        nvme_write_gb=$(echo "scale=2; ($nvme_write_raw * 512000) / (1024^3)" | bc)
        nvme_read_gb=$(echo "scale=2; ($nvme_read_raw * 512000) / (1024^3)" | bc)
        nvme_write_tb=$(echo "scale=4; $nvme_write_gb / 1024" | bc)
        nvme_read_tb=$(echo "scale=4; $nvme_read_gb / 1024" | bc)

        echo "• Total Ditulis (Lifetime) : $nvme_write_gb GB ($nvme_write_tb TBW)"
        echo "• Total Dibaca (Lifetime)  : $nvme_read_gb GB ($nvme_read_tb TB)"
    else
        echo "⚠️ Gagal membaca kode internal. Ini output mentahnya:"
        sudo nvme smart-log /dev/nvme0n1 | grep -iE 'data_units_written|data_units_read'
    fi
else
    echo "❌ SSD Internal tidak terdeteksi."
fi
echo "=================================================="
