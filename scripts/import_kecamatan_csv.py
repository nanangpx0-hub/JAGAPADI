import csv
import os
import re
import sys
import time
import datetime
import pymysql
def dotted_to_plain(x):
    return x.replace('.', '')
def parse_date(s):
    return datetime.datetime.strptime(s, '%Y-%m-%d').date()
def now_ts():
    return datetime.datetime.now().strftime('%Y%m%d_%H%M%S')
def send_email(subject, body):
    host = os.environ.get('SMTP_HOST')
    to_addr = os.environ.get('MAIL_TO')
    if not host or not to_addr:
        return False
    import smtplib
    from email.mime.text import MIMEText
    msg = MIMEText(body)
    msg['Subject'] = subject
    msg['From'] = os.environ.get('SMTP_USER', 'noreply@example.com')
    msg['To'] = to_addr
    port = int(os.environ.get('SMTP_PORT', '25'))
    user = os.environ.get('SMTP_USER')
    pwd = os.environ.get('SMTP_PASS')
    s = smtplib.SMTP(host, port)
    try:
        if user and pwd:
            s.starttls()
            s.login(user, pwd)
        s.sendmail(msg['From'], [to_addr], msg.as_string())
    finally:
        s.quit()
    return True
def load_csv_rows(path):
    rows = []
    with open(path, newline='', encoding='utf-8') as f:
        r = csv.reader(f)
        buf = list(r)
        start_idx = 0
        if buf and buf[0] and buf[0][0].strip().lower().startswith('# deskripsi kolom'):
            for i in range(1, min(len(buf), 100)):
                if buf[i] and buf[i][0].strip().lower() == 'id_kabupaten':
                    start_idx = i
                    break
        header = [x.strip() for x in buf[start_idx]]
        for row in buf[start_idx+1:]:
            if not row or all([(c is None or str(c).strip()=='') for c in row]):
                continue
            d = {}
            for i, h in enumerate(header):
                if i < len(row):
                    d[h] = str(row[i]).strip()
                else:
                    d[h] = ''
            rows.append(d)
    return rows
def validate_record(rec):
    errors = []
    rid = rec.get('id_kecamatan','')
    kid = rec.get('id_kabupaten','')
    nkab = rec.get('nama_kabupaten','')
    nkec = rec.get('nama_kecamatan','')
    tgl = rec.get('tanggal_update','')
    if not re.match(r'^\d{2}\.\d{2}$', kid):
        errors.append(('FORMAT_ID_KAB', rid, 'id_kabupaten invalid'))
    if not nkab:
        errors.append(('EMPTY_NAMA_KAB', rid, 'nama_kabupaten empty'))
    if not re.match(r'^\d{2}\.\d{2}\.\d{2}$', rid):
        errors.append(('FORMAT_ID_KEC', rid, 'id_kecamatan invalid'))
    if not nkec:
        errors.append(('EMPTY_NAMA_KEC', rid, 'nama_kecamatan empty'))
    try:
        d = parse_date(tgl)
        if d > datetime.date.today():
            errors.append(('DATE_FUTURE', rid, 'tanggal_update > today'))
    except Exception:
        errors.append(('DATE_FORMAT', rid, 'tanggal_update invalid'))
    jp = rec.get('jumlah_penduduk','')
    lw = rec.get('luas_wilayah','')
    if jp:
        try:
            if int(float(jp)) < 0:
                errors.append(('NEGATIVE_JP', rid, 'jumlah_penduduk negative'))
        except Exception:
            errors.append(('NUMERIC_JP', rid, 'jumlah_penduduk invalid'))
    if lw:
        try:
            if float(lw) < 0:
                errors.append(('NEGATIVE_LW', rid, 'luas_wilayah negative'))
        except Exception:
            errors.append(('NUMERIC_LW', rid, 'luas_wilayah invalid'))
    for k in ['id_kabupaten','nama_kabupaten','id_kecamatan','nama_kecamatan','ibukota_kecamatan']:
        if rec.get(k) is not None:
            rec[k] = rec[k].strip()
    return errors
def main():
    if len(sys.argv) < 2:
        print('Usage: python scripts/import_kecamatan_csv.py <path_to_csv>')
        sys.exit(1)
    path = sys.argv[1]
    ts = now_ts()
    log_path = f'logs/log_import_{ts}.csv'
    err_path = f'logs/error_{ts}.csv'
    os.makedirs('logs', exist_ok=True)
    rows = load_csv_rows(path)
    start = datetime.datetime.now()
    conn = pymysql.connect(host='localhost', user='root', password='', database='bpsjembe_jagapadi', charset='utf8mb4', autocommit=False)
    cur = conn.cursor()
    processed = 0
    success = 0
    failed = 0
    errors_all = []
    try:
        for rec in rows:
            processed += 1
            errs = validate_record(rec)
            if errs:
                failed += 1
                errors_all.extend(errs)
                continue
            kid_plain = dotted_to_plain(rec['id_kabupaten'])
            kec_plain = dotted_to_plain(rec['id_kecamatan'])
            cur.execute('SELECT id FROM master_kabupaten WHERE kode_kabupaten = %s AND deleted_at IS NULL', (kid_plain,))
            rkab = cur.fetchone()
            if not rkab:
                failed += 1
                errors_all.append(('KAB_NOT_FOUND', rec['id_kecamatan'], 'Kabupaten kode tidak ditemukan'))
                continue
            kab_id = rkab[0]
            cur.execute('SELECT id FROM master_kecamatan WHERE kode_kecamatan = %s AND deleted_at IS NULL', (kec_plain,))
            rkec = cur.fetchone()
            if rkec:
                cur.execute('UPDATE master_kecamatan SET nama_kecamatan = %s, updated_by = %s, updated_at = NOW() WHERE id = %s', (rec['nama_kecamatan'], 1, rkec[0]))
                success += 1
                continue
            cur.execute('SELECT id FROM master_kecamatan WHERE kabupaten_id = %s AND nama_kecamatan = %s AND deleted_at IS NULL', (kab_id, rec['nama_kecamatan']))
            rkec2 = cur.fetchone()
            if rkec2:
                cur.execute('UPDATE master_kecamatan SET kode_kecamatan = %s, updated_by = %s, updated_at = NOW() WHERE id = %s', (kec_plain, 1, rkec2[0]))
                success += 1
                continue
            cur.execute('INSERT INTO master_kecamatan (kabupaten_id, nama_kecamatan, kode_kecamatan, created_by) VALUES (%s, %s, %s, %s)', (kab_id, rec['nama_kecamatan'], kec_plain, 1))
            success += 1
        conn.commit()
    except Exception as e:
        conn.rollback()
        print('CRITICAL ERROR:', str(e))
        failed = processed - success
        errors_all.append(('CRITICAL', 'N/A', str(e)))
    end = datetime.datetime.now()
    with open(log_path, 'w', newline='', encoding='utf-8') as f:
        w = csv.writer(f)
        w.writerow(['timestamp_start', start.strftime('%Y-%m-%d %H:%M:%S')])
        w.writerow(['timestamp_end', end.strftime('%Y-%m-%d %H:%M:%S')])
        w.writerow(['total_processed', processed])
        w.writerow(['total_success', success])
        w.writerow(['total_failed', failed])
        w.writerow([])
        w.writerow(['kode_error','id_record','pesan_error'])
        for e in errors_all:
            w.writerow(list(e))
    if errors_all:
        with open(err_path, 'w', newline='', encoding='utf-8') as f:
            w = csv.writer(f)
            w.writerow(['kode_error','id_record','pesan_error'])
            for e in errors_all:
                w.writerow(list(e))
    ratio = (failed / processed) * 100 if processed else 0
    if ratio > 10:
        send_email('Import Kecamatan Warning', f'Error ratio {ratio:.2f}% on {ts}')
    print('Waktu mulai:', start.strftime('%Y-%m-%d %H:%M:%S'))
    print('Waktu selesai:', end.strftime('%Y-%m-%d %H:%M:%S'))
    print('Total record diproses:', processed)
    print('Sukses:', success)
    print('Gagal:', failed)
    print('Log:', log_path)
    if errors_all:
        print('Error file:', err_path)
if __name__ == '__main__':
    main()
