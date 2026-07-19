{{-- Simple page / status motion. Safe for CDN Tailwind pages and the main layout. --}}
<style>
    @media (prefers-reduced-motion: no-preference) {
        @keyframes shamandora-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes page-enter {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes status-card-enter {
            from {
                opacity: 0;
                transform: translateY(16px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes overlay-fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes overlay-fade-out {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        @keyframes logo-soft-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.03); }
        }

        @keyframes loading-label-pulse {
            0%, 100% { opacity: 0.55; }
            50% { opacity: 1; }
        }

        .page-enter {
            animation: page-enter 0.35s ease-out both;
        }

        .status-card-enter {
            animation: status-card-enter 0.45s ease-out both;
        }

        .loading-overlay-enter {
            animation: overlay-fade-in 0.2s ease-out both;
        }

        .loading-overlay-exit {
            animation: overlay-fade-out 0.18s ease-in both;
        }

        .loading-logo-pulse {
            animation: logo-soft-pulse 1.6s ease-in-out infinite;
        }

        .loading-label-pulse {
            animation: loading-label-pulse 1.2s ease-in-out infinite;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .page-enter,
        .status-card-enter,
        .loading-overlay-enter,
        .loading-overlay-exit,
        .loading-logo-pulse,
        .loading-label-pulse {
            animation: none !important;
        }
    }
</style>
