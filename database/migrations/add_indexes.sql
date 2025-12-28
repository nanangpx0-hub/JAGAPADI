-- Database Indexes for Performance Optimization
-- Execute this script to add indexes for frequently queried columns

-- Indexes for laporan_hama table
CREATE INDEX IF NOT EXISTS idx_laporan_hama_status ON laporan_hama(status);
CREATE INDEX IF NOT EXISTS idx_laporan_hama_user_id ON laporan_hama(user_id);
CREATE INDEX IF NOT EXISTS idx_laporan_hama_master_opt_id ON laporan_hama(master_opt_id);
CREATE INDEX IF NOT EXISTS idx_laporan_hama_tanggal ON laporan_hama(tanggal);
CREATE INDEX IF NOT EXISTS idx_laporan_hama_created_at ON laporan_hama(created_at);
CREATE INDEX IF NOT EXISTS idx_laporan_hama_kabupaten_id ON laporan_hama(kabupaten_id);
CREATE INDEX IF NOT EXISTS idx_laporan_hama_kecamatan_id ON laporan_hama(kecamatan_id);
CREATE INDEX IF NOT EXISTS idx_laporan_hama_desa_id ON laporan_hama(desa_id);
CREATE INDEX IF NOT EXISTS idx_laporan_hama_tingkat_keparahan ON laporan_hama(tingkat_keparahan);
CREATE INDEX IF NOT EXISTS idx_laporan_hama_verified_by ON laporan_hama(verified_by);
CREATE INDEX IF NOT EXISTS idx_laporan_hama_verified_at ON laporan_hama(verified_at);

-- Composite indexes for common queries
CREATE INDEX IF NOT EXISTS idx_laporan_hama_status_tanggal ON laporan_hama(status, tanggal);
CREATE INDEX IF NOT EXISTS idx_laporan_hama_status_opt ON laporan_hama(status, master_opt_id);
CREATE INDEX IF NOT EXISTS idx_laporan_hama_wilayah ON laporan_hama(kabupaten_id, kecamatan_id, desa_id);

-- Indexes for users table
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);
CREATE INDEX IF NOT EXISTS idx_users_created_at ON users(created_at);

-- Indexes for master_opt table
CREATE INDEX IF NOT EXISTS idx_master_opt_jenis ON master_opt(jenis);
CREATE INDEX IF NOT EXISTS idx_master_opt_kode_opt ON master_opt(kode_opt);
CREATE INDEX IF NOT EXISTS idx_master_opt_nama_opt ON master_opt(nama_opt);

-- Indexes for master_kabupaten table
CREATE INDEX IF NOT EXISTS idx_master_kabupaten_kode ON master_kabupaten(kode_kabupaten);
CREATE INDEX IF NOT EXISTS idx_master_kabupaten_deleted_at ON master_kabupaten(deleted_at);

-- Indexes for master_kecamatan table
CREATE INDEX IF NOT EXISTS idx_master_kecamatan_kabupaten_id ON master_kecamatan(kabupaten_id);
CREATE INDEX IF NOT EXISTS idx_master_kecamatan_kode ON master_kecamatan(kode_kecamatan);
CREATE INDEX IF NOT EXISTS idx_master_kecamatan_deleted_at ON master_kecamatan(deleted_at);

-- Indexes for master_desa table
CREATE INDEX IF NOT EXISTS idx_master_desa_kecamatan_id ON master_desa(kecamatan_id);
CREATE INDEX IF NOT EXISTS idx_master_desa_kode ON master_desa(kode_desa);
CREATE INDEX IF NOT EXISTS idx_master_desa_deleted_at ON master_desa(deleted_at);

-- Indexes for activity_log table
CREATE INDEX IF NOT EXISTS idx_activity_log_user_id ON activity_log(user_id);
CREATE INDEX IF NOT EXISTS idx_activity_log_action ON activity_log(action);
CREATE INDEX IF NOT EXISTS idx_activity_log_created_at ON activity_log(created_at);
CREATE INDEX IF NOT EXISTS idx_activity_log_table_name ON activity_log(table_name);
CREATE INDEX IF NOT EXISTS idx_activity_log_record_id ON activity_log(record_id);

-- Composite index for activity log queries
CREATE INDEX IF NOT EXISTS idx_activity_log_user_created ON activity_log(user_id, created_at);

-- Indexes for audit_log_wilayah table (if exists)
CREATE INDEX IF NOT EXISTS idx_audit_log_wilayah_user_id ON audit_log_wilayah(user_id);
CREATE INDEX IF NOT EXISTS idx_audit_log_wilayah_created_at ON audit_log_wilayah(created_at);
CREATE INDEX IF NOT EXISTS idx_audit_log_wilayah_table_name ON audit_log_wilayah(table_name);

-- Full-text indexes for search (MySQL 5.6+)
-- Uncomment if you need full-text search capabilities
-- ALTER TABLE laporan_hama ADD FULLTEXT INDEX idx_laporan_hama_search (lokasi, catatan);
-- ALTER TABLE master_opt ADD FULLTEXT INDEX idx_master_opt_search (nama_opt, deskripsi);
-- ALTER TABLE users ADD FULLTEXT INDEX idx_users_search (nama_lengkap, username, email);

-- Show all indexes
-- SELECT 
--     TABLE_NAME,
--     INDEX_NAME,
--     COLUMN_NAME,
--     SEQ_IN_INDEX
-- FROM 
--     INFORMATION_SCHEMA.STATISTICS
-- WHERE 
--     TABLE_SCHEMA = DATABASE()
-- ORDER BY 
--     TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

