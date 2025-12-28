-- Migration: Add classification fields to master_opt table
-- Date: 2025-12-26
-- Description: Adds scientific classification, quarantine status, danger level, and reference fields

-- Add scientific name (nama ilmiah)
ALTER TABLE master_opt ADD COLUMN IF NOT EXISTS nama_ilmiah VARCHAR(255) NULL AFTER nama_opt;

-- Add local/common name (nama lokal)
ALTER TABLE master_opt ADD COLUMN IF NOT EXISTS nama_lokal VARCHAR(255) NULL AFTER nama_ilmiah;

-- Add taxonomy classification fields
ALTER TABLE master_opt ADD COLUMN IF NOT EXISTS kingdom VARCHAR(100) DEFAULT 'Animalia' AFTER nama_lokal;
ALTER TABLE master_opt ADD COLUMN IF NOT EXISTS filum VARCHAR(100) NULL AFTER kingdom;
ALTER TABLE master_opt ADD COLUMN IF NOT EXISTS kelas VARCHAR(100) NULL AFTER filum;
ALTER TABLE master_opt ADD COLUMN IF NOT EXISTS ordo VARCHAR(100) NULL AFTER kelas;
ALTER TABLE master_opt ADD COLUMN IF NOT EXISTS famili VARCHAR(100) NULL AFTER ordo;
ALTER TABLE master_opt ADD COLUMN IF NOT EXISTS genus VARCHAR(100) NULL AFTER famili;

-- Add quarantine status (OPTK = Organisme Pengganggu Tumbuhan Karantina)
-- OPTK A1: OPT yang belum ada di Indonesia
-- OPTK A2: OPT yang sudah ada tapi terbatas
-- OPTK B: OPT yang sudah tersebar
ALTER TABLE master_opt ADD COLUMN IF NOT EXISTS status_karantina ENUM('Tidak','OPTK A1','OPTK A2','OPTK B') DEFAULT 'Tidak' AFTER genus;

-- Add danger level
ALTER TABLE master_opt ADD COLUMN IF NOT EXISTS tingkat_bahaya ENUM('Rendah','Sedang','Tinggi','Sangat Tinggi') DEFAULT 'Sedang' AFTER status_karantina;

-- Add reference/source field
ALTER TABLE master_opt ADD COLUMN IF NOT EXISTS referensi TEXT NULL AFTER tingkat_bahaya;

-- Create index for faster searches
CREATE INDEX IF NOT EXISTS idx_master_opt_nama_ilmiah ON master_opt(nama_ilmiah);
CREATE INDEX IF NOT EXISTS idx_master_opt_status_karantina ON master_opt(status_karantina);
CREATE INDEX IF NOT EXISTS idx_master_opt_tingkat_bahaya ON master_opt(tingkat_bahaya);

-- Update existing data with default values where applicable
UPDATE master_opt SET kingdom = 'Animalia' WHERE jenis = 'Hama' AND kingdom IS NULL;
UPDATE master_opt SET kingdom = 'Fungi' WHERE jenis = 'Penyakit' AND kingdom IS NULL;
UPDATE master_opt SET kingdom = 'Plantae' WHERE jenis = 'Gulma' AND kingdom IS NULL;
