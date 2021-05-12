<script>
    function openByWireCertificateModal(){
        document.getElementById('bywire-certificate--modalCenter').classList.add('show');
        document.getElementById('bywire-certificate--modal-backdrop').classList.add('show');
    }

    function closeByWireCertificateModal(){
        document.getElementById('bywire-certificate--modalCenter').classList.remove('show');
        document.getElementById('bywire-certificate--modal-backdrop').classList.remove('show');
    }
</script>

<div class="bywire-certificate--message-container" data-blockchain-status="{$message}" onClick="openByWireCertificateModal()" style="cursor:pointer;">{$message}</div>

