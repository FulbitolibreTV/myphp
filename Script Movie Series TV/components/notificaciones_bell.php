<button class="noti-bell" onclick="window.location.href='reporte_list.php'">
  <i class="fas fa-bell"></i>
  <div class="count" id="notiCount">0</div>
</button>

<style>
.noti-bell {
  position: fixed;
  bottom: 30px;
  right: 30px;
  background: #1a237e;
  color: white;
  border: none;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  font-size: 1.6rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(0,0,0,0.3);
  z-index: 1500;
}
.noti-bell .count {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #ff3d00;
  color: white;
  border-radius: 50%;
  padding: 3px 7px;
  font-size: 0.8rem;
  font-weight: bold;
}
</style>

<script>
let audio = new Audio('/audio/noti.mp3');
audio.volume = 0.5;
let isRinging = false;

function ringBell(){
  let bell = document.querySelector('.noti-bell');
  bell.style.transition = 'transform 0.3s ease';
  bell.style.transform = 'scale(1.3) rotate(-15deg)';
  setTimeout(()=> bell.style.transform = '', 400);

  audio.play().catch(err => console.log("Autoplay bloqueado:", err));
}

function updateNotifications(){
  fetch('check_notificaciones.php?rand=' + Math.random())
    .then(res => res.ok ? res.json() : {})
    .then(data => {
      const total = data.total || 0;
      const nuevos = data.nuevos || false;
      const countElem = document.getElementById('notiCount');

      countElem.textContent = total;

      if(total > 0){
        // si no está sonando, inicia ciclo
        if(!isRinging){
          isRinging = true;
          keepRinging();
        }
      } else {
        isRinging = false;
      }
    })
    .catch(err => {
      console.error('Error al cargar notificaciones:', err);
      document.getElementById('notiCount').textContent = '0';
      isRinging = false;
    });
}

function keepRinging(){
  if(!isRinging) return;
  ringBell();
  setTimeout(keepRinging, 5000); // cada 5 segundos volverá a sonar y vibrar si sigue activo
}

setInterval(updateNotifications, 5000);
updateNotifications();
</script>
