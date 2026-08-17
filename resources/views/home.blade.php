@extends('layouts.app')

@section('title', 'DONA ÓRGANOS PANAMÁ')
@section('body_class', 'home-page institutional combined')

@section('content')
<a class="skip-link" href="#contenido-principal">Ir al contenido principal</a>

<div class="topline">
    <div class="home-wrap">
        <span class="topline-brand"><span class="topline-heart" aria-hidden="true">♥</span>DONA ÓRGANOS PANAMÁ</span>
        <span>Panamá · Atención: 503-6033</span>
    </div>
</div>

<header class="site-header">
    <nav class="navbar navbar-expand-lg" aria-label="Navegación principal">
        <div class="container-xl home-navigation">
            <a class="navbar-brand nav-donor-mark" href="#inicio" aria-label="Ir al inicio">
                <img src="{{ asset('images/icono-donacion-manos-corazon.png') }}" alt="" width="48" height="48">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Abrir navegación">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavigation">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#aspectos-legales">Aspectos legales</a></li>
                    <li class="nav-item"><a class="nav-link" href="#mitos">Mitos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#preguntas">Preguntas frecuentes</a></li>
                    <li class="nav-item"><a class="nav-link" href="#historias">Testimonios</a></li>
                </ul>
                <div class="nav-actions ms-lg-3">
                    <a class="button button-primary" href="{{ route('registration.identity') }}">Registrarme</a>
                    <a class="admin-link" href="{{ auth()->check() ? route('admin.dashboard') : route('login') }}" aria-label="{{ auth()->check() ? 'Abrir panel administrativo' : 'Acceso de administración' }}">
                        <span aria-hidden="true">🔒</span><span>Administración</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>

<main id="contenido-principal">
    <section id="inicio" class="hero-section">
        <div class="container-xl hero-grid">
            <div class="hero-copy">
                <p class="hero-kicker">Información clara. Decisión segura.</p>
                <h1>Donar órganos es <span>un obsequio de vida.</span>
                    <img class="hero-gift-icon" src="{{ asset('images/icono-regalo-mano.png') }}" alt="" aria-hidden="true"></h1>
                <p>Conoce el proceso, resuelve tus dudas y registra tu voluntad de manera sencilla. Tu decisión será tratada con respeto, confidencialidad y respaldo institucional.</p>
            </div>
            <aside class="trust-panel" tabindex="0">
                <h2>Que debes saber antes de registrarte</h2>
                <ul class="checklist">
                    <li><b aria-hidden="true">✓</b><span>Ingresa los datos solicitados de tu cédula para iniciar de forma segura tu registro.</span></li>
                    <li><b aria-hidden="true">✓</b><span>Registra tus datos, contacto(s) de confianza y las preferencias de donación.</span></li>
                    <li><b aria-hidden="true">✓</b><span>Podrás modificar en cualquier momento tu voluntad siguiendo el procedimiento establecido.</span></li>
                    <li><b aria-hidden="true">✓</b><span>Ten presente comunicar tu decisión a familiares o contacto(s) de confianza.</span></li>
                    <li><b>✓</b><span>Hasta 8 vidas pueden ser salvadas por un solo donante.</span>
                    <div class="figures-icon">
                        <svg width="220" height="35" viewBox="0 0 220 35" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <g id="persona">
                                    <circle cx="0" cy="8" r="3.5"/>
                                    <path d="M0 13c-2 0-3.5 1.5-3.5 3.5V24h1.5v8h4v-8h1.5v-7.5c0-2-1.5-3.5-3.5-3.5z"/>
                                </g>
                            </defs>
                            <use href="#persona" x="15" fill="#fbbf24"/>
                            <use href="#persona" x="42" fill="#bfdbfe"/>
                            <use href="#persona" x="69" fill="#bfdbfe"/>
                            <use href="#persona" x="96" fill="#bfdbfe"/>
                            <use href="#persona" x="123" fill="#bfdbfe"/>
                            <use href="#persona" x="150" fill="#bfdbfe"/>
                            <use href="#persona" x="177" fill="#bfdbfe"/>
                            <use href="#persona" x="204" fill="#bfdbfe"/>
                        </svg>
                    </div>
                    </li>
                </ul>
            </aside>
        </div>
    </section>

    <section id="aspectos-legales" class="section-space section-muted">
        <div class="container-xl">
            <p class="section-eyebrow text-center">Decide con información</p>
            <h2 class="section-title">Aspectos Legales Importantes</h2>
            <p class="section-note">Selecciona o enfoca una tarjeta para consultar su información.</p>
            <div class="row g-4">
                @forelse ($legalContents as $content)
                    <div class="col-md-6 col-xl-4">
                        <article class="legal-card" tabindex="0">
                            <span class="legal-number">{{ $loop->iteration }}</span>
                            <span class="legal-visual legal-visual-{{ (($loop->iteration - 1) % 6) + 1 }}">
                                @if ($content->media?->media_type === 'image')
                                    <img class="legal-uploaded-image" src="{{ $content->media->url }}" alt="{{ $content->media->alt_text }}">
                                @endif
                                <span class="legal-detail">
                                    <div class="cms-rich-text">{!! app(\App\Services\ContentHtmlSanitizer::class)->sanitize($content->body) !!}</div>
                                </span>
                            </span>
                            <h3>{{ $content->title }}</h3>
                        </article>
                    </div>
                @empty
                    <p class="empty-content">El contenido legal se encuentra en actualización.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section id="mitos" class="section-space">
        <div class="container">
            <p class="section-eyebrow text-center">Hablemos con claridad</p>
            <h2 class="section-title">Mitos y Tabúes Desmentidos</h2>
            <div class="accordion-list" data-collapsible-list data-visible-items="4">
                @forelse ($myths as $content)
                    <details class="content-accordion myth-accordion{{ $loop->iteration > 4 ? ' additional-content' : '' }}" @if ($loop->iteration > 4) hidden @endif>
                        <summary>{{ $content->title }}</summary>
                        <div class="accordion-answer">
                            <strong>La realidad</strong>
                            <div class="cms-rich-text">{!! app(\App\Services\ContentHtmlSanitizer::class)->sanitize($content->body) !!}</div>
                        </div>
                    </details>
                @empty
                    <p class="empty-content">Los mitos y realidades se encuentran en actualización.</p>
                @endforelse
                @if ($myths->count() > 4)
                    <button class="show-more" type="button" aria-expanded="false">
                        <span class="show-more-label">Otros mitos</span><span aria-hidden="true">＋</span>
                    </button>
                @endif
            </div>
        </div>
    </section>

    <section id="preguntas" class="section-space section-muted">
        <div class="container">
            <p class="section-eyebrow text-center">Respuestas para decidir</p>
            <h2 class="section-title">Preguntas Frecuentes</h2>
            <div class="accordion-list" data-collapsible-list data-visible-items="4">
                @forelse ($faqs as $content)
                    <details class="content-accordion faq-accordion{{ $loop->iteration > 4 ? ' additional-content' : '' }}" @if ($loop->iteration > 4) hidden @endif>
                        <summary>{{ $content->title }}</summary>
                        <div class="accordion-answer">
                            <strong>Respuesta</strong>
                            <div class="cms-rich-text">{!! app(\App\Services\ContentHtmlSanitizer::class)->sanitize($content->body) !!}</div>
                        </div>
                    </details>
                @empty
                    <p class="empty-content">Las preguntas frecuentes se encuentran en actualización.</p>
                @endforelse
                @if ($faqs->count() > 4)
                    <button class="show-more" type="button" aria-expanded="false">
                        <span class="show-more-label">Otras preguntas</span><span aria-hidden="true">＋</span>
                    </button>
                @endif
            </div>
        </div>
    </section>

    <section id="historias" class="section-space">
        <div class="container-xl">
            <p class="section-eyebrow text-center">Voces que inspiran</p>
            <h2 class="section-title">Historias Personales</h2>
            <div class="row g-4 justify-content-center">
                @forelse ($stories as $story)
                    <div class="col-lg-5">
                        <article class="story-card h-100">
                            @if ($story->media?->media_type === 'video')
                                <video class="story-video" controls preload="metadata">
                                    <source src="{{ $story->media->url }}" type="{{ $story->media->mime_type }}">
                                    Tu navegador no permite reproducir este video.
                                </video>
                            @endif
                            <span class="story-quote" aria-hidden="true">“</span>
                            <div class="story-body cms-rich-text">{!! app(\App\Services\ContentHtmlSanitizer::class)->sanitize($story->body) !!}</div>
                            <strong>{{ $story->title }}</strong>
                            @if ($story->subtitle)
                                <span class="story-subtitle">{{ $story->subtitle }}</span>
                            @endif
                        </article>
                    </div>
                @empty
                    <p class="empty-content">Las historias personales se encuentran en actualización.</p>
                @endforelse
            </div>
            <p class="testimonial-disclaimer">Testimonios ilustrativos pendientes de validación y autorización institucional.</p>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container-xl">
        <div class="row g-5 footer-main">
            <div class="col-lg-6">
                <h2>DONA ÓRGANOS PANAMÁ</h2>
                <div class="footer-heading-line"></div>
                <p>Infórmate, conversa con tus seres queridos y deja un legado de vida.</p>
            </div>
            <div class="col-lg-6">
                <h2>Contactos Oficiales en Panamá</h2>
                <div class="footer-heading-line"></div>
                <address class="footer-contacts">
                    <p><strong>CSS - Coordinación Nacional de Trasplantes:</strong> <a href="tel:5036033">☎ 503-6033</a></p>
                    <p><strong>MINSA - Depto. de Trasplantes:</strong> <a href="mailto:trasplantes@minsa.gob.pa">✉ trasplantes@minsa.gob.pa</a></p>
                </address>
            </div>
        </div>
        <div class="footer-bottom">© {{ now()->year }} DONA ÓRGANOS PANAMÁ. Portal educativo adaptado al marco institucional.</div>
    </div>
</footer>

<button id="backToTop" class="back-to-top" type="button" aria-label="Volver al inicio">↑</button>

@endsection
