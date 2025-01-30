<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archdiocesan Shrine of Santa Rosa De Lima Db</title>
    <link rel="stylesheet" href="css/style-indexxx.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="website icon" type="png" href="css/img/LOGO.png" id="logo">

    <style>

        #title-roman {
            font-size: 25px;
            font-family: Open sans;
            color:#fff;
            font-style: italic;
            
        }

        #title-address {
            font-size: 20px;
            font-family:Georgia, 'Times New Roman', Times, serif;
            color:#fff;
        }

        #title-address + hr#title-line {
            border: 1px solid black; /* Black color border */
            margin-top: 20px; /* Space above the line */
            margin-bottom: 20px; /* Space below the line */
            width: 80%; /* Adjust the width of the line */
            margin-left: auto; /* Center the line */
            margin-right: auto; /* Center the line */
            background-color: aliceblue;
        }
        #pic2 {
            background-image: url(css/img/baptism.jpg);
            background-size: cover;
            background-repeat: no-repeat;
            box-shadow:0 10px 6px rgba(0,0,0,.5);
        } 

        #pic3 {
            background-image: url(css/img/pinoy-wedding.jpg);
            background-size: cover;
            background-repeat: no-repeat;
            box-shadow:0 10px 6px rgba(0,0,0,.5);
        } 

        #pic4 {
            background-image: url(css/img/Holy_Eucharist.jpeg);
            background-size: cover;
            background-repeat: no-repeat;
            box-shadow:0 10px 6px rgba(0,0,0,.5);
        } 

        #pic5 {
            background-image: url(css/img/Blessing_HolyWater.jpg);
            background-size: cover;
            background-repeat: no-repeat;
            box-shadow:0 10px 6px rgba(0,0,0,.5);
        } 

        #pic6 {
            background-image: url(css/img/confirmation-1.jpg);
            background-size: cover;
            background-repeat: no-repeat;
            box-shadow:0 10px 6px rgba(0,0,0,.5);
        }
        #pic7 {
            background-image: url(css/img/annointing-pinoy.jpeg);
            background-size: cover;
            background-repeat: no-repeat;
            box-shadow:0 10px 6px rgba(0,0,0,.5);
        }
        #pic8 {
            background-image: url(css/img/mass-intention.jpg);
            background-size: cover;
            background-repeat: no-repeat;
            box-shadow:0 10px 6px rgba(0,0,0,.5);
        }
        .btn-r-up {
            position:fixed;
            z-index: 1000000;
            top:82%;
            left:90%;
        }
        .btn-r-up  .bx  {
            font-size:4rem;
            color:#fff;
            background:#ffd90088;
            border-radius:50%;
            box-shadow:0 5px 10px rgba(0,0,0,.5);
            cursor:pointer;
        }
        
    </style>

  
</head>
<body>

<header class="fadeUp" id="header-confg" style="position:absolute;">
    <div class="div" style="display:flex; flex-direction:row; justify-content:center; align-items:center;">
     <a href="admin.php" id="logs"><img src="css/img/LOGO.png" alt="Logo"  style="width: 80px; border-radius:50px;" id="logs"></a>
     <h3 style="color:#fff; letter-spacing:2px;font-family: 'Playfair Display', serif;">Reservation Management System</h3>
    </div>
     <input type="checkbox" id="check">
     <label for="check" class="icons">
     <i class='bx bx-menu' id="menu-icon"></i>
     <i class='bx bx-x' id="close-icon"></i>
     </label> 
    <nav>
        <a href="#home" class="anim8">Home</a>    
        <a href="#about" class="anim8">About us</a>
        <a href="#contact" class="anim8">Contact</a>
        <a href="signin.php" class="anim8">Sign In</a>
        <a href="#services" id="ser-txt" class="text5">Services</a>
    
    </nav>
</header>
<section id="home" >
<div class="btn-r-up">
<i class='bx bx-chevron-up'></i>
</div>
    <img src="css/img/mainbg.jpg" alt="background" style='opacity: .3; position:fixed; '>
    <div class="cont1" >
        <div id="main-cont" class="fadeUp"> 
        
        <div class="updiv" id="updiv">
        <div class="div">
            <img src="css/img/display.png" alt="" style="width:350px; height:450px; margin-top:-35px; border-radius:10px; opacity:.8;">
        </div>

        <div class="div2">

        <p id="title-roman"> The Roman Catholic Archdiocese of Cebu </p> <br>
        <h2 id="title-loc" style="font-family:'Times New Roman', Times, serif; font-size:50px;"> Archdiocesan Shrine of Santa Rosa de Lima Daanbantayan </h2> <br>
        <p id="title-address">Santa Rosa Street, Poblacion, Daanbantayan, Cebu</p> 
        <hr id=title-line>

        <br>
        <div id="button-text" class="fadeUp">
            <a href="signup.php" id="get-txt" >Get Started</a>
        </div>
        <div class="animate-flick">
        <i class='bx bx-chevrons-down'></i>
        </div>
        </div>
        </div>
        </div>

   
    </div>
</section>

<div class="cont" id="about" style="position:relative; z-index:1000;">
    <div class="lay0">
    <h1 style="cursor:default;"> About Us</h1>
    <hr>
    </div>

    <div class="about-sec">
        <div class="text-area">
            <p>Welcome to the Web-based Reservation Management System for the Archdiocesan Shrine of Santa Rosa de Lima in Daanbantayan, 
                Cebu. Our mission is to provide an easy, efficient, and accessible way for parishioners and visitors to reserve 
                spaces for religious events, ceremonies, and activities within our beloved shrine.
            </p>
            <br>
            <p>
            Founded in 1858, the Archdiocesan Shrine of Santa Rosa de Lima has been a spiritual haven for generations of the faithful. 
            As part of our ongoing efforts to embrace modern technology and enhance our service to the community, this system allows 
            parishioners to quickly and effortlessly book reservations for sacramental offers, including Baptism, Confirmation, 
            Holy Eucharist, Confession, Anointing of the Sick, and Matrimony, as well as other essential services such as Blessing 
            and Burial.
            </p>
             <br>
             <p>
             Whether you're a local parishioner or a visitor from afar, our reservation system ensures that your 
             experience with us is smooth, convenient, and memorable. We are committed to providing a 
             hassle-free process for all your reservation needs, allowing you to focus more on your spiritual journey 
             and less on logistics.
            </p>
            </p>
            <p>Our mission is to facilitate seamless coordination and efficient management of church 
             facilities and resources, ensuring that every event and service is conducted smoothly and in
             accordance with our community's needs and values.
            </p>
            <br>
             <br>
            <h2>Who we are</h2>
            <br>
             <p>We are a dedicated team committed to supporting the mission and ministry of the Archdiocesan Shrine of Santa 
                Rosa de Lima through innovative technology solutions. Our system is designed with the specific needs of our 
                church community in mind, streamlining the reservation process for sacraments and services while upholding 
                the values of faith, tradition, and community. By embracing modern technology, we aim to simplify access to 
                essential church services such as Baptism, Matrimony, Holy Eucharist, and more, making it easier for parishioners
                 to engage with the church while maintaining the sacredness and integrity of these rituals.
            </p>
            <br>
            <p>
            Our goal is to provide a seamless experience that enhances the spiritual journey of each individual, ensuring 
            that technology serves as a tool to strengthen our community, not replace the personal connections that define 
            our faith. We are committed to fostering a welcoming, inclusive, and supportive environment for all, where everyone 
            feels valued and connected to the church. Through our system, we seek to make the church’s services more 
            accessible, efficient, and in line with the needs of today’s parishioners, while always remaining grounded in 
            the mission of the church to serve with love and compassion.
            </p>
             <br>
        </div>
    </div>

    <div class="lay1" id="services">
        <h1 style="cursor:default;"> Sacraments and Services Offers </h1>
        <hr>
    </div>
    <div class="lay2">

        <div class="dis1">
            <div class="serv-pic" id="pic2">
            </div>
            <br>
            <h1>Baptism</h1>
            <p>
            Baptism is a sacred and symbolic ceremony that marks the initiation of an individual into the Christian faith. 
            It is a solemn yet joyous occasion, often attended by family, friends, and the faith community. Through the act 
            of baptism, an individual is cleansed of original sin and welcomed into the spiritual journey, signifying a new 
            beginning in the faith.
            </p>
            <a href="baptism.php">Reserve Now</a>
        </div>

        <div class="dis1">
            <div class="serv-pic" id="pic6">
            </div>
             <br>
            <h1>Confirmation</h1>
            <p>
             Confirmation is a sacred and meaningful religious ceremony observed in many Christian denominations,
             symbolizing the affirming and strengthening of one's faith commitment. Typically occurring during 
             adolescence or early adulthood, confirmation is often viewed as a rite of passage within the church 
             community.
            </p>
            <a href="signin.php">Reserve Now</a>
        </div>

    </div>

    <div class="lay3">

    <div class="dis1">
            <div class="serv-pic" id="pic4">
            </div>
             <br>
            <h1>Holy Eucharist</h1>
            <p>
            Holy Eucharist, also known as Communion or the Lord's Supper, is a deeply significant sacrament observed in 
            Christian faith. It is a solemn and sacred ritual in which believers partake of consecrated bread and wine, 
            representing the Body and Blood of Jesus Christ. This sacrament offers spiritual nourishment, bringing participants 
            into deeper communion with Christ and strengthening their faith.
            </p>
            <a href="signin.php">Reserve Now</a>
        </div>

    <div class="dis1">
            <div class="serv-pic" id="pic3">
            </div>
             <br>
            <h1>Wedding</h1>
            <p>
            A wedding is a joyous and ceremonial event that symbolizes the union of two individuals in marriage. It is a 
            sacred commitment of love and faith. 
            The ceremony expresses the couple’s lifelong dedication to each other and reflects a deep bond, open to the 
            possibility of raising children in faith. Through this sacrament, affirms their vows and 
            commitment, seeking divine blessings for their journey together.
            </p>
            <a href="signin.php">Reserve Now</a>
        </div>

    </div>
    
    <div class="lay4">

    <div class="dis1">
            <div class="serv-pic" id="pic7">
            </div>
             <br>
            <h1>Anointing of the sick</h1>
            <p>
            Anointing of the Sick is a sacrament offered to individuals who are seriously ill or elderly, bringing comfort, 
            spiritual strength, and healing. During this sacrament, the individual is anointed and prayed over, with 
            the belief that it provides spiritual healing and helps the person grow closer to God, especially in times of 
            suffering.
            </p>
            <a href="signin.php">Reserve Now</a>
        </div>

        <div class="dis1">
            <div class="serv-pic" id="pic1">
            </div>
             <br>
            <h1>Funeral Services / Burial</h1>
            <p>        
            Burial is a solemn ceremony marking the final resting place of a deceased individual. It holds deep cultural, 
            religious, and personal significance, reflecting beliefs about the afterlife. The ritual often includes prayers
            honoring the life and memory of the departed. It signifies the belief in the sanctity of the body and the soul’s 
            journey toward reunion with God in the afterlife.
            </p>
            <a href="signin.php">Reserve Now</a>
           
        </div>

    </div>

    <div class="lay5">

    <div class="dis1">
            <div class="serv-pic" id="pic5">            
            </div>
             <br>
            <h1>Blessing</h1>
            <p>
             A blessing is a ceremonial or ritual act that invokes divine favor, protection, or guidance 
             upon individuals, objects, places, or events. Found across various cultures and religions, 
             blessings are expressions of goodwill and spiritual significance, often performed by religious 
             leaders, elders, or individuals recognized for their spiritual authority.
            </p>
            <a href="signin.php">Reserve Now</a>
        </div>

        <div class="dis1">
            <div class="serv-pic" id="pic8">            
            </div>
             <br>
            <h1>Mass Intention</h1>
            <p>
            Mass Intention Services offer a meaningful way to remember loved ones, 
      seek blessings, or offer prayers for special intentions during a holy 
      Mass. This spiritual practice strengthens our connection with God and 
      the Church.

            </p>
            <a href="signin.php">Reserve Now</a>
        </div>

    </div>
    <div class="lay6" id="contact">
    <h1 style="cursor:default;"> Contact Us</h1>
    <hr>
    </div>

    <div class="lay7">
        <div class="text-area" id="text-bg">
            <div class="icon-msg">
                <a href="" style="font-size:21px;"><i class='bx bx-envelope'></i> assrldb.2022@gmail.com</a>
                <a href="" style="font-size:21px;"><i class='bx bxs-phone'></i>09606704726</a>
                <a href="feedback.php" style="font-size:21px;"><i class='bx bx-comment-dots'></i>Feedback</a>
            </div>
            <br>
        <p>For inquiries, support, or feedback, please don’t hesitate to reach out to our team. We are here to assist you 
            and ensure that your experience with our Reservation Management System is as seamless and effective as 
            possible.
        </p>
         <br>
         Thank you for choosing our system to serve and strengthen our church community. 
         We look forward to supporting you in creating memorable and meaningful experiences within our church family.
        <br>
         <br>
        <h3>Our Commitment</h3>
             <br>
            <p>
              We are committed to continuously improving our system based on feedback and evolving needs within 
              our church community. Our goal is to enhance the overall experience of planning and attending 
              church events, ensuring that every member and visitor feels valued and supported in their
              spiritual journey.
            </p>
        </div>
    </div>
    <script>
    // Get the button
    const btnUp = document.querySelector('.btn-r-up');

    // Add a scroll event listener
    window.addEventListener('scroll', () => {
        // Check the scroll position
        if (window.scrollY > 300) {
            // Show the button if scrolled down more than 300px
            btnUp.style.display = 'block';
        } else {
            // Hide the button if scrolled up
            btnUp.style.display = 'none';
        }
    });

    // Add a click event listener to the button
    btnUp.addEventListener('click', () => {
        // Scroll smoothly to the top of the page
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Initial button visibility (hidden by default)
    btnUp.style.display = 'none';
</script>


</div>
<footer style="position:relative; z-index:1000;">
<p>&copy; 2025 Archdiocesan Shrine of Santa Rosa de Lima Daanbantayan. All Rights Reserved.</p>
</footer>
</body>
</html>