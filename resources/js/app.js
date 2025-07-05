import {livewire_hot_reload} from 'virtual:livewire-hot-reload'
import Swal from 'sweetalert2'

// Expose SweetAlert2 to global scope for use in Blade templates
window.Swal = Swal;

livewire_hot_reload();