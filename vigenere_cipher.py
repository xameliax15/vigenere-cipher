def huruf_ke_angka(huruf):
    return ord(huruf.upper()) - ord('A')

def angka_ke_huruf(angka):
    return chr((angka % 26) + ord('A'))

def bersihkan_teks(teks):
    return ''.join(filter(str.isalpha, teks)).upper()

def perluas_kunci(kunci, panjang):
    kunci_bersih = bersihkan_teks(kunci)
    return (kunci_bersih * (panjang // len(kunci_bersih) + 1))[:panjang]

def enkripsi_vigenere(plaintext, kunci):
    plaintext_bersih = bersihkan_teks(plaintext)
    kunci_diperluas = perluas_kunci(kunci, len(plaintext_bersih))

    ciphertext = ''
    for p, k in zip(plaintext_bersih, kunci_diperluas):
        pi = huruf_ke_angka(p)
        ki = huruf_ke_angka(k)
        ci = (pi + ki) % 26
        ciphertext += angka_ke_huruf(ci)
    return ciphertext

# Masukkan plaintext dan kunci
plaintext = """
Di sebuah kampus yang asri, mahasiswa berlalu-lalang menuju ruang kelas masing-masing. 
Di taman, sekelompok mahasiswa duduk berdiskusi, membahas tugas yang akan segera dikumpulkan. 
Seorang dosen berjalan cepat, membawa tumpukan buku menuju ruang kuliah. 
Suara bel berbunyi, menandakan perkuliahan akan segera dimulai. 
Di perpustakaan, beberapa mahasiswa sibuk membaca buku referensi, mencatat poin-poin penting. 
Sementara itu, di kantin, beberapa mahasiswa menikmati makan siang sambil berbincang santai. 
Matahari siang bersinar terang, menciptakan bayangan panjang di sepanjang jalur pejalan kaki yang teduh oleh pohon rindang.
"""

kunci = "AMELIA BALQIS HERMAWAN"

ciphertext = enkripsi_vigenere(plaintext, kunci)
print("Ciphertext:\n", ciphertext)