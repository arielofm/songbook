<?php
$uploadSuccess = isset($uploadSuccess) ? (bool)$uploadSuccess : false;
$uploadFailed = isset($uploadFailed) ? (bool)$uploadFailed : false;
$developerGeneralPhotos = is_array($developerGeneralPhotos ?? null) ? $developerGeneralPhotos : [];
$developerArtPhotos = is_array($developerArtPhotos ?? null) ? $developerArtPhotos : [];
$developerCoverPhoto = is_array($developerCoverPhoto ?? null) ? $developerCoverPhoto : null;
$developerAvatarPhoto = is_array($developerAvatarPhoto ?? null) ? $developerAvatarPhoto : null;
$developerCoverPhotoUrl = $developerCoverPhoto ? '?action=developer-photo&id=' . (int)$developerCoverPhoto['id'] : '';
$developerAvatarPhotoUrl = $developerAvatarPhoto ? '?action=developer-photo&id=' . (int)$developerAvatarPhoto['id'] : '/assets/developer-profile.png';
?>


<?php if ($uploadSuccess): ?>
    <div class="notice success">Photo uploaded successfully and stored in SQLite.</div>
<?php endif; ?>
<?php if ($uploadFailed): ?>
    <div class="notice">Upload failed. Use JPG, PNG, WEBP, or GIF up to 8MB.</div>
<?php endif; ?>

<section class="developer-profile-page">
    <article class="developer-profile-shell">
        <header class="developer-cover" aria-label="Developer cover photo">
            <div
                class="developer-cover-art"
                aria-hidden="true"
                <?= $developerCoverPhotoUrl !== '' ? 'style="background-image: linear-gradient(180deg, rgba(8, 30, 84, 0.18), rgba(8, 30, 84, 0.36)), url(\'' . h($developerCoverPhotoUrl) . '\');"' : '' ?>
            ></div>
            <div class="developer-cover-actions">
                <button
                    type="button"
                    class="developer-icon-btn"
                    data-open-modal="cover-upload-modal"
                    aria-label="Cover photo options"
                    title="Cover photo options"
                >
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 7H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M4 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M4 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </header>

        <div class="developer-floating-header">
            <section class="developer-profile-top">
                <section class="developer-identity-row">
                    <div class="developer-identity-main">
                        <div class="developer-avatar-wrap">
                            <img class="developer-avatar" src="<?= h($developerAvatarPhotoUrl) ?>" alt="John Ariel Rullan profile photo">
                            <button type="button" class="developer-icon-btn developer-avatar-change" data-open-modal="avatar-upload-modal" aria-label="Change profile photo" title="Change profile photo">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7.5C4 6.67157 4.67157 6 5.5 6H8L9.2 4.7C9.48362 4.39275 9.88293 4.21875 10.3 4.21875H13.7C14.1171 4.21875 14.5164 4.39275 14.8 4.7L16 6H18.5C19.3284 6 20 6.67157 20 7.5V17.5C20 18.3284 19.3284 19 18.5 19H5.5C4.67157 19 4 18.3284 4 17.5V7.5Z" stroke="currentColor" stroke-width="1.7"></path><circle cx="12" cy="12.5" r="3.5" stroke="currentColor" stroke-width="1.7"></circle></svg>
                            </button>
                        </div>

                        <div class="developer-identity-copy">
                            <div class="developer-name-row">
                                <h1>John Ariel Rullan</h1>
                                <span class="developer-verified" aria-label="Verified profile" title="Verified account">
                                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M10 1.8L12.22 3.7L15.1 3.48L16.08 6.2L18.6 7.62L17.62 10.34L18.6 13.06L16.08 14.48L15.1 17.2L12.22 16.98L10 18.9L7.78 16.98L4.9 17.2L3.92 14.48L1.4 13.06L2.38 10.34L1.4 7.62L3.92 6.2L4.9 3.48L7.78 3.7L10 1.8Z" fill="currentColor"></path>
                                        <path d="M6.3 10.2L8.7 12.4L13.5 7.7" stroke="#ffffff" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                            </div>
                            <p class="developer-handle">Visual Artist • Musician • Software Developer</p>
                            <p class="developer-follower-copy">Professional profile • Personal creative ecosystem • Built with a modern social-style presentation</p>
                            <p class="developer-bio">Building practical digital systems for musicians and communities while creating visual art and music with a clean, expressive, and professional identity.</p>
                            <div class="developer-meta-pills">
                                <span class="pill">Lives in Davao City</span>
                                <span class="pill">From Moalboal, Cebu</span>
                                <span class="pill">Founder of SongShelf</span>
                            </div>
                        </div>
                    </div>

                    <div class="developer-profile-actions">
                        <button
                            type="button"
                            class="developer-icon-btn developer-profile-upload-btn"
                            data-open-modal="general-upload-modal"
                            aria-label="Upload photo"
                            title="Upload photo"
                        >
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 16V7.5M8.7 10.8L12 7.5L15.3 10.8M5 17.5H19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                    </div>
                </section>

                <div class="developer-tabs-wrap">
                    <nav class="developer-tabs" aria-label="Developer profile sections">
                        <a href="#about" class="is-active">About</a>
                        <a href="#featured">Featured</a>
                        <a href="#education">Education</a>
                        <a href="#achievements">Achievements</a>
                        <a href="#software">Projects</a>
                        <a href="#photos">Photos</a>
                        <a href="#links">Links</a>
                    </nav>
                </div>
            </section>
        </div>

        <section class="developer-grid">
            <aside class="developer-left-col stack-form">
                <article class="developer-card pastel-lavender" id="about">
                    <h3>Intro</h3>
                    <ul class="developer-list developer-intro-list">
                        <li>
                            <svg class="developer-info-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z" stroke="currentColor" stroke-width="1.7"></path><path d="M12 8V12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"></path><circle cx="12" cy="16" r="1" fill="currentColor"></circle></svg>
                            <span>Born <strong>May 6, 1996</strong></span>
                        </li>
                        <li>
                            <svg class="developer-info-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21S19 15.4 19 10A7 7 0 1 0 5 10C5 15.4 12 21 12 21Z" stroke="currentColor" stroke-width="1.7"></path><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.7"></circle></svg>
                            <span>Lives in <strong>Davao City</strong></span>
                        </li>
                        <li>
                            <svg class="developer-info-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19.5V8.5L12 4.5L20 8.5V19.5" stroke="currentColor" stroke-width="1.7"></path><path d="M9 19.5V13H15V19.5" stroke="currentColor" stroke-width="1.7"></path></svg>
                            <span>Also connected to <strong>Moalboal, Cebu</strong></span>
                        </li>
                        <li>
                            <svg class="developer-info-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 6V18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"></path><path d="M8 10.5C8 9.11929 9.11929 8 10.5 8H14C15.1046 8 16 8.89543 16 10C16 11.1046 15.1046 12 14 12H10C8.89543 12 8 12.8954 8 14C8 15.1046 8.89543 16 10 16H13.5C14.8807 16 16 14.8807 16 13.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"></path></svg>
                            <span>Profession: <strong>Visual Artist / Musician / Software Developer</strong></span>
                        </li>
                    </ul>
                </article>

                <article class="developer-card pastel-sky">
                    <div class="developer-contact-header">
                        <div>
                            <h3>Contact References</h3>
                            <p class="subtle">Profiles, repositories, and quick contact references.</p>
                        </div>
                        <button type="button" class="developer-icon-btn" data-open-modal="contact-add-modal" aria-label="Add contact reference" title="Add contact reference">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"></path></svg>
                        </button>
                    </div>
                    <div class="developer-contact-grid">
                        <a class="developer-contact-link" href="tel:+639451548442">
                            <div class="developer-contact-main">
                                <span class="developer-contact-brand phone" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M6.9 4.8C7.15 4.28 7.68 4 8.24 4H10.6C11.2 4 11.72 4.36 11.95 4.91L12.86 7.15C13.08 7.69 12.98 8.31 12.61 8.74L11.43 10.13C12.1 11.45 13.19 12.52 14.55 13.17L15.91 12.04C16.35 11.67 16.97 11.57 17.52 11.8L19.84 12.74C20.39 12.97 20.75 13.49 20.75 14.08V16.48C20.75 17.04 20.47 17.57 19.95 17.82C18.85 18.35 17.6 18.57 16.45 18.43C13.98 18.13 11.78 16.97 9.82 15.01C7.86 13.05 6.71 10.86 6.4 8.39C6.26 7.22 6.37 5.92 6.9 4.8Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <div>
                                    <strong>Phone</strong>
                                    <small>(+63) 945 154 8442</small>
                                </div>
                            </div>
                            <span>Call</span>
                        </a>
                        <a class="developer-contact-link" href="mailto:johnarielrullan@outlook.com">
                            <div class="developer-contact-main">
                                <span class="developer-contact-brand email" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <rect x="3.8" y="5.4" width="16.4" height="13.2" rx="2.2" stroke="currentColor" stroke-width="1.7"/>
                                        <path d="M4.7 7L12 12.8L19.3 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <div>
                                    <strong>Email</strong>
                                    <small>johnarielrullan@outlook.com</small>
                                </div>
                            </div>
                            <span>Send</span>
                        </a>
                        <a class="developer-contact-link" href="https://github.com/arielofm" target="_blank" rel="noreferrer">
                            <div class="developer-contact-main">
                                <span class="developer-contact-brand github" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 3C7.03 3 3 7.13 3 12.23C3 16.31 5.58 19.77 9.16 21C9.61 21.08 9.77 20.8 9.77 20.56C9.77 20.34 9.76 19.62 9.76 18.84C7.27 19.39 6.74 17.75 6.74 17.75C6.33 16.67 5.73 16.39 5.73 16.39C4.9 15.81 5.79 15.82 5.79 15.82C6.71 15.89 7.2 16.79 7.2 16.79C8.01 18.23 9.34 17.82 9.87 17.57C9.95 16.96 10.19 16.55 10.45 16.31C8.46 16.08 6.37 15.28 6.37 11.69C6.37 10.67 6.73 9.84 7.32 9.19C7.23 8.96 6.92 8.02 7.41 6.75C7.41 6.75 8.17 6.5 9.75 7.6C10.47 7.39 11.24 7.29 12 7.29C12.76 7.29 13.53 7.39 14.25 7.6C15.83 6.5 16.59 6.75 16.59 6.75C17.08 8.02 16.77 8.96 16.68 9.19C17.27 9.84 17.63 10.67 17.63 11.69C17.63 15.29 15.53 16.08 13.53 16.3C13.87 16.59 14.17 17.15 14.17 18.01C14.17 19.24 14.16 20.24 14.16 20.56C14.16 20.8 14.32 21.09 14.78 21C18.42 19.75 21 16.3 21 12.23C21 7.13 16.97 3 12 3Z"/>
                                    </svg>
                                </span>
                                <div>
                                    <strong>GitHub</strong>
                                    <small>@arielofm</small>
                                </div>
                            </div>
                            <span>Open</span>
                        </a>
                        <a class="developer-contact-link" href="https://github.com/VotivateApp/Votivate" target="_blank" rel="noreferrer">
                            <div class="developer-contact-main">
                                <span class="developer-contact-brand votivate" aria-hidden="true">
                                    <img src="/assets/votivate-logo.png" alt="">
                                </span>
                                <div>
                                    <strong>Votivate Repository</strong>
                                    <small>Capstone mobile voting app</small>
                                </div>
                            </div>
                            <span>View</span>
                        </a>
                    </div>
                </article>

                <article class="developer-card pastel-rose" id="links">
                    <div class="developer-links-header">
                        <div>
                            <h3>Social Media & Links</h3>
                            <p class="subtle">Public platforms and profile destinations.</p>
                        </div>
                        <button type="button" class="developer-icon-btn" data-open-modal="social-add-modal" aria-label="Add social link" title="Add social link">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="developer-social-grid">
                        <a class="developer-social-link" href="https://www.facebook.com/ayee.rullan/" target="_blank" rel="noreferrer">
                            <div class="developer-social-main">
                                <span class="developer-social-brand facebook" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M13.5 21V12.8H16.3L16.72 9.6H13.5V7.56C13.5 6.63 13.76 6 15.09 6H16.84V3.14C16.54 3.1 15.5 3 14.28 3C11.73 3 9.98 4.56 9.98 7.42V9.6H7.5V12.8H9.98V21H13.5Z"/>
                                    </svg>
                                </span>
                                <div>
                                    <strong>Facebook</strong>
                                    <!-- <small>facebook.com/</small> -->
                                </div>
                            </div>
                            <span>↗</span>
                        </a>

                        <a class="developer-social-link" href="https://www.instagram.com/sijuanofm/" target="_blank" rel="noreferrer">
                            <div class="developer-social-main">
                                <span class="developer-social-brand instagram" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <rect x="4.5" y="4.5" width="15" height="15" rx="4.2" stroke="currentColor" stroke-width="1.8"/>
                                        <circle cx="12" cy="12" r="3.6" stroke="currentColor" stroke-width="1.8"/>
                                        <circle cx="17.2" cy="6.9" r="1.1" fill="currentColor"/>
                                    </svg>
                                </span>
                                <div>
                                    <strong>Instagram</strong>
                                    <!-- <small>instagram.com/arielofm</small> -->
                                </div>
                            </div>
                            <span>↗</span>
                        </a>

                        <a class="developer-social-link" href="https://github.com/arielofm" target="_blank" rel="noreferrer">
                            <div class="developer-social-main">
                                <span class="developer-social-brand github" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 3C7.03 3 3 7.13 3 12.23C3 16.31 5.58 19.77 9.16 21C9.61 21.08 9.77 20.8 9.77 20.56C9.77 20.34 9.76 19.62 9.76 18.84C7.27 19.39 6.74 17.75 6.74 17.75C6.33 16.67 5.73 16.39 5.73 16.39C4.9 15.81 5.79 15.82 5.79 15.82C6.71 15.89 7.2 16.79 7.2 16.79C8.01 18.23 9.34 17.82 9.87 17.57C9.95 16.96 10.19 16.55 10.45 16.31C8.46 16.08 6.37 15.28 6.37 11.69C6.37 10.67 6.73 9.84 7.32 9.19C7.23 8.96 6.92 8.02 7.41 6.75C7.41 6.75 8.17 6.5 9.75 7.6C10.47 7.39 11.24 7.29 12 7.29C12.76 7.29 13.53 7.39 14.25 7.6C15.83 6.5 16.59 6.75 16.59 6.75C17.08 8.02 16.77 8.96 16.68 9.19C17.27 9.84 17.63 10.67 17.63 11.69C17.63 15.29 15.53 16.08 13.53 16.3C13.87 16.59 14.17 17.15 14.17 18.01C14.17 19.24 14.16 20.24 14.16 20.56C14.16 20.8 14.32 21.09 14.78 21C18.42 19.75 21 16.3 21 12.23C21 7.13 16.97 3 12 3Z"/>
                                    </svg>
                                </span>
                                <div>
                                    <strong>GitHub</strong>
                                    <!-- <small>github.com/arielofm</small> -->
                                </div>
                            </div>
                            <span>↗</span>
                        </a>
                    </div>
                </article>

                <article class="developer-card pastel-mint">
                    <h3>Address</h3>
                    <ul class="developer-list">
                        <li>01A-Belen Road, Pampanga Executive Homes, Vicente Hizon Sr., Buhangin District, Davao City, Davao del Sur, 8000</li>
                        <li>Poblacion West, Moalboal, Cebu, 6032</li>
                    </ul>
                </article>
            </aside>

            <main class="developer-main-col stack-form">
                <article class="developer-card" id="featured">
                    <div class="developer-section-header">
                        <div>
                            <h3>Featured</h3>
                            <p class="subtle">More visual, more current, and closer to the way modern social profiles highlight identity.</p>
                        </div>
                    </div>
                    <div class="developer-story-bar">
                        <article class="developer-story-card story-art">
                            <div class="developer-story-tint"></div>
                            <span>Creative Work</span>
                            <h4>Visual storytelling and exhibit-ready pieces</h4>
                            <p>Artwork, presentation, and visual identity built for personal and public-facing work.</p>
                        </article>
                        <article class="developer-story-card story-music">
                            <div class="developer-story-tint"></div>
                            <span>Music Systems</span>
                            <h4>Tools built for real musician workflows</h4>
                            <p>From rehearsal to organization, each feature is shaped by practical music use.</p>
                        </article>
                        <article class="developer-story-card story-code">
                            <div class="developer-story-tint"></div>
                            <span>Software Projects</span>
                            <h4>Clean systems with practical human value</h4>
                            <p>Modern applications focused on clarity, function, and creative productivity.</p>
                        </article>
                    </div>
                </article>

                <article class="developer-card pastel-peach" id="education">
                    <h3>Educational Background</h3>
                    <div class="developer-timeline">
                        <article class="developer-education-story">
                            <div class="developer-education-separator"></div>
                            <div class="developer-education-card sky">
                                <h4>Bachelor of Science in Information Technology</h4>
                                <p class="subtle">2020 - 2024</p>
                                <p>Assumption College of Davao</p>
                                <p class="subtle">J.P. Cabaguio Avenue, Davao City</p>
                            </div>
                        </article>
                        <article class="developer-education-story">
                            <div class="developer-education-separator"></div>
                            <div class="developer-education-card lavender">
                                <h4>Philosophy Studies</h4>
                                <p class="subtle">2017 - 2018</p>
                                <p>Notre Dame of Kidapawan College</p>
                                <p class="subtle">Datu Ingkal St., Kidapawan City, North Cotabato</p>
                            </div>
                        </article>
                        <article class="developer-education-story">
                            <div class="developer-education-separator"></div>
                            <div class="developer-education-card peach">
                                <h4>Associate in Industrial Technology major in Computer Technology</h4>
                                <p class="subtle">2012 - 2014</p>
                                <p>Cebu Technological University - Moalboal Campus (formerly CSCST)</p>
                                <p class="subtle">Poblacion West, Moalboal, Cebu</p>
                            </div>
                        </article>
                    </div>
                </article>

               <article class="developer-card pastel-lavender" id="achievements">
                    <header class="developer-card-head-row">
                        <div>
                            <h3>Achievements</h3>
                            <p class="subtle">
                                Milestones, recognitions, exhibit work, and meaningful personal builds.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="developer-icon-btn"
                            data-open-modal="art-upload-modal"
                            aria-label="Upload exhibit photo"
                            title="Upload exhibit photo"
                        >
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"></path>
                            </svg>
                        </button>
                    </header>

                    <ul class="developer-list developer-achievement-list">
                        <li>
                            <strong>Best Capstone</strong>
                            <span>Group: Katribo+ · Role: UI/UX Designer &amp; Assistant Programmer</span>
                        </li>
                        <li>
                            <strong>Art Exhibit for a Cause</strong>
                            <span>Participated with 3 entries</span>
                        </li>
                        <li>
                            <strong>SONGShelf</strong>
                            <span>A SongBookPro-inspired software built for personal use and practical music organization.</span>
                        </li>
                    </ul>

                    <section class="developer-achievement-gallery">
                        <header class="developer-card-head-row">
                            <h4>Art Exhibit Photos</h4>
                        </header>

                        <div class="developer-photo-gallery">
                            <?php if ($developerArtPhotos === []): ?>
                                <p class="subtle">No uploaded exhibit photos yet.</p>
                            <?php else: ?>
                                <?php foreach ($developerArtPhotos as $photo): ?>
                                    <figure class="developer-photo-item">
                                        <img
                                            src="?action=developer-photo&id=<?= (int) $photo['id'] ?>"
                                            alt="<?= h((string) ($photo['title'] ?: 'Art Exhibit Photo')) ?>"
                                        >
                                    </figure>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                </article>

                <article class="developer-card pastel-sky" id="software">
                    <h3>Projects & Software Developed</h3>
                    <div class="developer-software-grid">
                        <article class="developer-software-item songshelf">
                            <div class="developer-project-top">
                                <img src="/assets/logo.png" alt="SongShelf logo" class="developer-project-logo">
                                <div>
                                    <h4 class="developer-project-title">SONGShelf</h4>
                                    <p class="subtle">Musician's song library inspired by real performance and reference workflows.</p>
                                </div>
                            </div>
                            <p>Built for personal use as a SongBookPro-inspired software experience focused on organizing songs, references, and musician-friendly access.</p>
                            <div class="developer-language-stack">
                                <span class="developer-language-chip"><img src="https://cdn.simpleicons.org/php" alt="PHP icon">PHP</span>
                                <span class="developer-language-chip"><img src="https://cdn.simpleicons.org/sqlite" alt="SQLite icon">SQLite</span>
                                <span class="developer-language-chip"><img src="https://cdn.simpleicons.org/html5" alt="HTML icon">HTML</span>
                                <span class="developer-language-chip"><img src="https://cdn.simpleicons.org/css" alt="CSS icon">CSS</span>
                                <span class="developer-language-chip"><img src="https://cdn.simpleicons.org/javascript" alt="JavaScript icon">JavaScript</span>
                            </div>
                        </article>

                        <article class="developer-software-item votivate">
                            <div class="developer-project-top">
                                <img src="/assets/votivate-logo.png" alt="Votivate logo" class="developer-project-logo">
                                <div>
                                    <h4 class="developer-project-title">Votivate</h4>
                                    <p class="subtle">Capstone mobile voting application designed for structured digital participation.</p>
                                </div>
                            </div>
                            <p>A multi-language mobile app project that combines product design, interface logic, and implementation across several technologies.</p>
                            <div class="developer-language-stack">
                                <span class="developer-language-chip"><img src="https://cdn.simpleicons.org/flutter" alt="Flutter icon">Flutter</span>
                                <span class="developer-language-chip"><img src="https://cdn.simpleicons.org/dart" alt="Dart icon">Dart</span>
                                <span class="developer-language-chip"><img src="https://cdn.simpleicons.org/cplusplus" alt="C++ icon">C++</span>
                                <span class="developer-language-chip"><img src="https://cdn.simpleicons.org/swift" alt="Swift icon">Swift</span>
                                <span class="developer-language-chip"><img src="https://cdn.simpleicons.org/html5" alt="HTML icon">HTML</span>
                                <span class="developer-language-chip"><img src="https://cdn.simpleicons.org/c" alt="C icon">C</span>
                            </div>
                        </article>
                    </div>
                </article>

                <article class="developer-card pastel-mint" id="photos">
                    <div class="developer-card-head-row">
                        <div>
                            <h3>Photos</h3>
                            <p class="subtle">A cleaner media presentation inspired by modern profile galleries.</p>
                        </div>
                        <button type="button" class="developer-icon-btn" data-open-modal="general-upload-modal" aria-label="Add general photo" title="Add general photo">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"></path></svg>
                        </button>
                    </div>

                    <div class="developer-photo-gallery">
                        <?php if ($developerGeneralPhotos === []): ?>
                            <p class="subtle">No general photos uploaded yet.</p>
                        <?php else: ?>
                            <?php foreach ($developerGeneralPhotos as $photo): ?>
                                <figure class="developer-photo-item">
                                    <img src="?action=developer-photo&id=<?= (int)$photo['id'] ?>" alt="<?= h((string)($photo['title'] ?: 'Developer Photo')) ?>">
                                    <figcaption><?= h((string)($photo['title'] ?: 'Developer Photo')) ?></figcaption>
                                </figure>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>
            </main>
        </section>
    </article>
</section>

<div class="developer-modal" id="general-upload-modal" aria-hidden="true">
    <div class="developer-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="general-upload-title">
        <div class="developer-modal-header">
            <h3 class="developer-modal-title" id="general-upload-title">Upload Photo</h3>
        </div>
        <div class="developer-modal-body">
            <form method="post" action="?action=upload-developer-photo" enctype="multipart/form-data" class="developer-upload-form">
                <input type="hidden" name="photo_category" value="general">
                <label>Photo title
                    <input type="text" name="photo_title" placeholder="Short caption">
                </label>
                <label>Select photo
                    <input type="file" name="developer_photo[]" accept="image/*" multiple required>
                </label>
                <div class="developer-modal-footer">
                    <button type="button" class="developer-action-btn secondary" data-close-modal="general-upload-modal">Cancel</button>
                    <button type="submit" class="developer-action-btn primary">Upload Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="developer-modal" id="art-upload-modal" aria-hidden="true">
    <div class="developer-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="art-upload-title">
        <div class="developer-modal-header">
            <h3 class="developer-modal-title" id="art-upload-title">Upload Art Exhibit Entry</h3>
        </div>
        <div class="developer-modal-body">
            <form method="post" action="?action=upload-developer-photo" enctype="multipart/form-data" class="developer-upload-form">
                <input type="hidden" name="photo_category" value="art_exhibit">
                <label>Photo title
                    <input type="text" name="photo_title" placeholder="Entry title or note">
                </label>
                <label>Select photo(s)
                    <input type="file" name="developer_photo[]" accept="image/*" multiple required>
                </label>
                <p class="subtle">Tip: hold Ctrl (Windows/Linux) or Command (Mac) to select multiple files.</p>
                <div class="developer-modal-footer">
                    <button type="button" class="developer-action-btn secondary" data-close-modal="art-upload-modal">Cancel</button>
                    <button type="submit" class="developer-action-btn primary">Upload Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="developer-modal" id="cover-upload-modal" aria-hidden="true">
    <div class="developer-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="cover-upload-title">
        <div class="developer-modal-header">
            <h3 class="developer-modal-title" id="cover-upload-title">Change Cover Photo</h3>
        </div>
        <div class="developer-modal-body">
            <form method="post" action="?action=upload-developer-photo" enctype="multipart/form-data" class="developer-upload-form">
                <input type="hidden" name="photo_category" value="cover">
                <label>Cover photo title
                    <input type="text" name="photo_title" placeholder="Optional cover photo title">
                </label>
                <label>Select cover photo
                    <input type="file" name="developer_photo" accept="image/*" required>
                </label>
                <div class="developer-modal-footer">
                    <button type="button" class="developer-action-btn secondary" data-close-modal="cover-upload-modal">Cancel</button>
                    <button type="submit" class="developer-action-btn primary">Save Cover Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="developer-modal" id="avatar-upload-modal" aria-hidden="true">
    <div class="developer-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="avatar-upload-title">
        <div class="developer-modal-header">
            <h3 class="developer-modal-title" id="avatar-upload-title">Change Profile Photo</h3>
        </div>
        <div class="developer-modal-body">
            <form method="post" action="?action=upload-developer-photo" enctype="multipart/form-data" class="developer-upload-form">
                <input type="hidden" name="photo_category" value="avatar">
                <label>Profile photo title
                    <input type="text" name="photo_title" placeholder="Optional profile photo title">
                </label>
                <label>Select profile photo
                    <input type="file" name="developer_photo" accept="image/*" required>
                </label>
                <div class="developer-modal-footer">
                    <button type="button" class="developer-action-btn secondary" data-close-modal="avatar-upload-modal">Cancel</button>
                    <button type="submit" class="developer-action-btn primary">Save Profile Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="developer-modal" id="contact-add-modal" aria-hidden="true">
    <div class="developer-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="contact-add-title">
        <div class="developer-modal-header">
            <h3 class="developer-modal-title" id="contact-add-title">Add Contact Reference</h3>
        </div>
        <div class="developer-modal-body">
            <form class="developer-add-form">
                <label>Reference title
                    <input type="text" name="contact_title" placeholder="Example: LinkedIn or Portfolio">
                </label>
                <label>Reference subtitle
                    <input type="text" name="contact_subtitle" placeholder="Example: /in/johnariel or personal website">
                </label>
                <label>Reference URL
                    <input type="url" name="contact_url" placeholder="https://">
                </label>
                <div class="developer-modal-footer">
                    <button type="button" class="developer-action-btn secondary" data-close-modal="contact-add-modal">Cancel</button>
                    <button type="button" class="developer-action-btn primary" data-close-modal="contact-add-modal">Done</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="developer-modal" id="social-add-modal" aria-hidden="true">
    <div class="developer-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="social-add-title">
        <div class="developer-modal-header">
            <h3 class="developer-modal-title" id="social-add-title">Add Social Link</h3>
        </div>
        <div class="developer-modal-body">
            <form class="developer-add-form">
                <label>Platform
                    <input type="text" name="social_title" placeholder="Example: TikTok, LinkedIn, Behance">
                </label>
                <label>Display URL
                    <input type="text" name="social_subtitle" placeholder="example.com/username">
                </label>
                <label>Profile URL
                    <input type="url" name="social_url" placeholder="https://">
                </label>
                <div class="developer-modal-footer">
                    <button type="button" class="developer-action-btn secondary" data-close-modal="social-add-modal">Cancel</button>
                    <button type="button" class="developer-action-btn primary" data-close-modal="social-add-modal">Done</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(() => {
    const openButtons = document.querySelectorAll('[data-open-modal]');
    const closeButtons = document.querySelectorAll('[data-close-modal]');
    const modals = document.querySelectorAll('.developer-modal');
    const tabLinks = document.querySelectorAll('.developer-tabs a');

    const openModal = (id) => {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = (id) => {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        if (!document.querySelector('.developer-modal.is-open')) {
            document.body.style.overflow = '';
        }
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', () => openModal(button.dataset.openModal));
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => closeModal(button.dataset.closeModal));
    });

    modals.forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal(modal.id);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            modals.forEach((modal) => closeModal(modal.id));
        }
    });

    const sections = [...tabLinks]
        .map((link) => document.querySelector(link.getAttribute('href')))
        .filter(Boolean);

    const updateActiveTab = () => {
        const offset = 170;
        let currentId = sections[0] ? '#' + sections[0].id : '';
        sections.forEach((section) => {
            if (window.scrollY + offset >= section.offsetTop) {
                currentId = '#' + section.id;
            }
        });
        tabLinks.forEach((link) => {
            link.classList.toggle('is-active', link.getAttribute('href') === currentId);
        });
    };

    window.addEventListener('scroll', updateActiveTab, { passive: true });
    updateActiveTab();
})();
</script>
