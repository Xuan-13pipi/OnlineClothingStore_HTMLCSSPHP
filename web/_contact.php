
<?php
require '_base.php';

include '_head.php';
?>

<link rel="stylesheet"  href="/css/Contact.css">

<?php
include '_nav.php';
?>

<title>Contact Us</title>


<main>
    <section class="contact-form">
        <h2>CONTACT US</h2>
        <form action="#popup" method="get">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone">

            <label for="message">Message</label>
            <textarea id="message" name="message" rows="5" required></textarea>


            <label for="options">Select an option</label>
            <select id="options" name="options">
                <option value="" selected>- Pick an option -</option>
                <option value="support">Customer Support</option>
                <option value="order">Order Issue</option>
                <option value="feedback">Feedback</option>
            </select>

            <button type="submit">SEND</button>
<div class="info">
            <p>This site is protected by hCaptcha and the hCaptcha Privacy Policy and Terms of Service apply.</p>
     </div>
     
        </form>

        <div id="popup" class="popup">
            <div class="popup-content">
                <h3>Send Successfully!</h3>
                <p>Thank you for contacting us. We will get back to you soon.</p>
                <a href="#" class="close-btn">OK</a>
            </div>
        </div>

    </section>
</main>


</form>

</body>

<?php include '_foot.php'; ?>