import {livewire_hot_reload} from 'virtual:livewire-hot-reload'
import '../../vendor/masmerise/livewire-toaster/resources/js'
import Swal from 'sweetalert2'  

window.Swal = Swal 

Alpine.start()
livewire_hot_reload();  

 