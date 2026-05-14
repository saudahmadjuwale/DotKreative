var menuBtn = document.getElementById('menuBtn');
var cancel = document.getElementById('cancel');
var mobNav = document.getElementById('mobNav');

menuBtn.addEventListener('click',()=>{
    mobNav.style.display = 'flex';
});
cancel.addEventListener('click',()=>{
    mobNav.style.display = 'none';
});
document.querySelectorAll('#mobNav a').forEach(link => {
    link.addEventListener('click', () => {
        mobNav.style.display = 'none';
    });
});