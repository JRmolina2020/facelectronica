<?php
require 'header.php';
?>
<?php
require 'footer.php';
?>
<script>
let timerInterval
Swal.fire({
    title: 'ERROR',
    icon: 'error',
    text: 'EXISTE UNA NOTA CON EL TOTAL EN 0 , ENVIE NOTA X NOTA, MENOS LA NOTA DEL VALOR 0',
    timer: 8000,
    timerProgressBar: true,
    onBeforeOpen: () => {
        Swal.showLoading()
        timerInterval = setInterval(() => {
            Swal.getContent().querySelector('b')
                .textContent = Swal.getTimerLeft()
        }, 100)
    },
    onClose: () => {
        clearInterval(timerInterval)
    }
}).then((result) => {
    if (
        result.dismiss === Swal.DismissReason.timer
    ) {
        window.location.assign("index.php")
    }
})
</script>