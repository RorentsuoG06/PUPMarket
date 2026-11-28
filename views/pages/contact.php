<section class="page-header">

    <div class="container text-center">

        <h1 class="fw-bold">
            Contact Us
        </h1>

        <p>
            Have a question? We'd love to hear from you.
        </p>

    </div>

</section>


<section class="py-5">

    <div class="container">

        <div class="row g-5">

            <div class="col-lg-5">

                <h2 class="fw-bold mb-4">
                    Get in Touch
                </h2>

                <div class="contact-info">

                    <div class="contact-item">

                        <i class="bi bi-geo-alt"></i>

                        <div>

                            <h6>Location</h6>

                            <p>
                                PUP Biñan Campus
                            </p>

                        </div>

                    </div>


                    <div class="contact-item">

                        <i class="bi bi-envelope"></i>

                        <div>

                            <h6>Email</h6>

                            <p>
                                pupmarket@example.com
                            </p>

                        </div>

                    </div>


                    <div class="contact-item">

                        <i class="bi bi-clock"></i>

                        <div>

                            <h6>Availability</h6>

                            <p>
                                Monday - Friday
                            </p>

                        </div>

                    </div>

                </div>


                <div class="mt-4">

                    <h5>
                        Follow Us
                    </h5>

                    <div class="social-icons">

                        <a href="#">
                            <i class="bi bi-facebook"></i>
                        </a>

                        <a href="#">
                            <i class="bi bi-instagram"></i>
                        </a>

                        <a href="#">
                            <i class="bi bi-messenger"></i>
                        </a>

                    </div>

                </div>

            </div>


            <div class="col-lg-7">

                <div class="contact-form-card">

                    <h3 class="fw-bold mb-4">
                        Send Us a Message
                    </h3>

                    <?php if ($message !== ""): ?>

                        <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>

                    <?php endif; ?>


                    <form method="POST"
                          action="actions/process_contact.php">

                        <div class="mb-3">

                            <label class="form-label">
                                Name
                            </label>

                            <input type="text"
                                   class="form-control"
                                   name="name"
                                   value="<?= htmlspecialchars($name ?? "") ?>"
                                   required>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input type="email"
                                   class="form-control"
                                   name="email"
                                   value="<?= htmlspecialchars($email ?? "") ?>"
                                   required>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Subject
                            </label>

                            <input type="text"
                                   class="form-control"
                                   name="subject"
                                   value="<?= htmlspecialchars($subject ?? "") ?>"
                                   required>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Message
                            </label>

                            <textarea class="form-control"
                                      name="message"
                                      rows="5"
                                      required><?= htmlspecialchars($messageText ?? "") ?></textarea>

                        </div>


                        <button type="submit"
                                class="btn btn-pup">

                            <i class="bi bi-send"></i>
                            Send Message

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</section>

