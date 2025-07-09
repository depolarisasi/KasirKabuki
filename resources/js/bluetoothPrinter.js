// resources/js/bluetoothPrinter.js

export default class BluetoothPrinter {
    constructor() {
        this.device = null;
        this.server = null;
        this.service = null;
        this.characteristic = null;
    }

    async connect() {
        try {
            console.log('Mencari printer Bluetooth...');
            this.device = await navigator.bluetooth.requestDevice({
                filters: [{
                    // UUID ini adalah untuk Serial Port Profile (SPP)
                    services: ['00001101-0000-1000-8000-00805f9b34fb']
                }],
                // optionalServices bisa ditambahkan jika printer Anda punya layanan lain
            });

            console.log('Menghubungkan ke GATT Server...');
            this.server = await this.device.gatt.connect();

            console.log('Mendapatkan Layanan (Service)...');
            this.service = await this.server.getPrimaryService('00001101-0000-1000-8000-00805f9b34fb');

            console.log('Mendapatkan Karakteristik (Characteristic)...');
            // UUID Characteristic untuk SPP seringkali berbeda sedikit dari Service UUID
            // '00001102-...' adalah salah satu yang umum, tapi mungkin perlu disesuaikan
            // Gunakan aplikasi seperti nRF Connect untuk menemukan UUID Characteristic yang benar jika ini gagal
            this.characteristic = await this.service.getCharacteristic('00001102-0000-1000-8000-00805f9b34fb');
            
            console.log('Printer terhubung!');
            return true;

        } catch (error) {
            console.error('Koneksi Gagal:', error);
            alert(`Koneksi Gagal: ${error.message}`);
            return false;
        }
    }

    async print(text) {
        if (!this.characteristic) {
            alert('Printer tidak terhubung. Silakan hubungkan terlebih dahulu.');
            return;
        }

        try {
            // Encode teks menjadi format yang bisa dikirim (UTF-8)
            const encoder = new TextEncoder();
            const data = encoder.encode(text + '\n\n\n'); // Tambahkan spasi agar kertas terdorong keluar

            // Kirim data ke printer dalam potongan kecil jika perlu (opsional, tergantung printer)
            const chunkSize = 512; // 512 byte per chunk
            for (let i = 0; i < data.length; i += chunkSize) {
                const chunk = data.slice(i, i + chunkSize);
                await this.characteristic.writeValueWithoutResponse(chunk);
            }

            console.log('Data berhasil dikirim untuk dicetak.');
            alert('Struk berhasil dicetak!');
        } catch (error) {
            console.error('Gagal Mencetak:', error);
            alert(`Gagal Mencetak: ${error.message}`);
        }
    }

    disconnect() {
        if (this.server && this.server.connected) {
            this.server.disconnect();
            console.log('Printer terputus.');
        }
    }
}