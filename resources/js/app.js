import {livewire_hot_reload} from 'virtual:livewire-hot-reload'
import '../../vendor/masmerise/livewire-toaster/resources/js'
import Swal from 'sweetalert2'
import BluetoothPrinter from './bluetoothPrinter.js'; // Impor di sini

// Buat satu instance printer dan pasang ke objek window
// agar bisa diakses dari listener Livewire dengan mudah.
window.myPrinter = new BluetoothPrinter();
window.Swal = Swal

livewire_hot_reload(); 
console.log('Aplikasi siap. Printer helper tersedia di window.myPrinter');


// Dengarkan event kustom yang akan kita kirim dari file Blade
document.addEventListener('bluetooth-print-request', async (event) => {
    // Ambil data struk dari detail event
    const receiptData = event.detail.data;
    const printButton = event.detail.button;

    if (!receiptData) {
        alert('Tidak ada data untuk dicetak.');
        return;
    }

    printButton.innerText = '🔄 Menghubungkan...';
    printButton.disabled = true;
    
    const printer = new BluetoothPrinter();
    const isConnected = await printer.connect();

    if (isConnected) {
        printButton.innerText = '🖨️ Mencetak...';
        
        // Panggil fungsi untuk membuat teks dari data JSON
        const textToPrint = generateReceiptText(receiptData);
        await printer.print(textToPrint);
    }

    printButton.innerText = '🖨️ Cetak via Bluetooth';
    printButton.disabled = false;
});

// Fungsi untuk membuat teks struk (pindahkan dari Blade ke sini)
function generateReceiptText(data) {
    const charWidth = 32; // Lebar karakter untuk kertas 58mm
    
    let text = '';
    const center = (str) => str.padStart((charWidth + str.length) / 2).padEnd(charWidth);
    const alignBetween = (left, right) => left.padEnd(charWidth - right.length) + right;
    const BOLD_ON = '\x1B\x45\x01';
    const BOLD_OFF = '\x1B\x45\x00';
    const CENTER_ALIGN = '\x1B\x61\x01';
    const LEFT_ALIGN = '\x1B\x61\x00';

    // --- Mulai menyusun struk ---
    text += CENTER_ALIGN;
    text += BOLD_ON + data.store.name + BOLD_OFF + '\n';
    if (data.store.address) text += data.store.address + '\n';
    text += LEFT_ALIGN;
    text += '-'.repeat(charWidth) + '\n';
    text += `Kasir: ${data.transaction.cashier}\n`;
    text += `Waktu: ${data.transaction.date}\n`;
    text += `No: ${data.transaction.code}\n`;
    text += '-'.repeat(charWidth) + '\n';

    data.items.forEach(item => {
        text += `${item.name}\n`;
        const priceLine = `${item.quantity} x ${ (item.total / item.quantity).toLocaleString('id-ID') }`;
        text += alignBetween(priceLine, item.total.toLocaleString('id-ID')) + '\n';
    });

    text += '-'.repeat(charWidth) + '\n';
    
    text += BOLD_ON;
    text += alignBetween('TOTAL', `Rp ${data.totals.final.toLocaleString('id-ID')}`) + '\n';
    text += BOLD_OFF;

    text += '\n' + CENTER_ALIGN;
    if (data.store.footer) text += data.store.footer + '\n';
    text += '---oOo---\n\n\n';

    return text;
}