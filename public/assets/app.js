// public/assets/js/app.js
document.addEventListener('DOMContentLoaded', function(){
  const roomForm = document.querySelector('#roomForm');
  if (roomForm) {
    roomForm.addEventListener('submit', function(e){
      const name = this.querySelector('[name="name"]').value.trim();
      const cap = parseInt(this.querySelector('[name="capacity"]').value||0,10);
      let errors = [];
      if (name.length < 3) errors.push('Nama minimal 3 karakter');
      if (cap <= 0) errors.push('Kapasitas harus > 0');
      if (errors.length) {
        e.preventDefault();
        showAlert(errors.join('<br>'), 'danger');
        window.scrollTo({top:0,behavior:'smooth'});
      }
    });
  }

  function showAlert(message, type='success'){
    const container = document.querySelector('#flash-container') || document.body;
    const div = document.createElement('div');
    div.className = `alert alert-${type} alert-dismissible`;
    div.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    container.prepend(div);
  }
});
