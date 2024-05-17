const header = document.querySelector('header');
const header_title = document.getElementById('header_title');
const d_btn1 = document.getElementById('d_btn1');
const d_btn2 = document.getElementById('d_btn2');
const slider_btn = document.querySelectorAll('.dot');


const backimg = {
    fimg: './styles/img/egypt-slider.d1f6d854.jpg',
    simg: './styles/img/mincraft.jpg',
    timg: './styles/img/callofdutty.jpg',
    ximg: './styles/img/fifa23.jpg',
    yimg: './styles/img/needspeed.jpg',
    zimg: './styles/img/pubg.jpg'

}


const slider_load = (index) => {
    const images = [backimg.fimg, backimg.simg, backimg.timg, backimg.ximg, backimg.yimg, backimg.zimg]
    const titles = ["GRAND THEFT AUTO V", "MINECRAFT", "CALL OF DUTY", "FIFA 23", "NEED FOR SPEED", "PUBG"]

    header.style.background = `url(${images[index]}) no-repeat center center/cover`;
    header_title.innerText = titles[index];

    slider_btn.forEach((btn, i) => {
        btn.style.background = i === index ? "#fff" : "rgb(184,184,184,0.1)"
    })

    d_btn1.href = "#"
    d_btn2.href = "#"
};

let currentIndex = 0;

const nextSlide = () => {
    currentIndex = (currentIndex + 1) % slider_btn.length;
    slider_load(currentIndex);
};

slider_btn.forEach((btn, index) => {
    btn.addEventListener('click', () => {
        currentIndex = index;
        slider_load(currentIndex);
    });
});

setInterval(nextSlide, 5000);
slider_load(currentIndex);