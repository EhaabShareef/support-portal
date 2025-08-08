{{-- First-run loading overlay --}}
<div x-data="loadingOverlay()" 
     x-show="showOverlay" 
     x-init="init()"
     @dashboard-ready.window="markReady()"
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-neutral-50/95 dark:bg-neutral-900/95 backdrop-blur-sm"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-500"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     aria-busy="true"
     role="status"
     aria-live="polite"
     x-trap.inert.noscroll="showOverlay"
     style="display: none;">
     
    <div class="text-center space-y-8 max-w-md mx-auto px-8">
        {{-- Animated Circuit Icon --}}
        <div class="flex justify-center">
            <svg class="w-16 h-16 text-sky-600/80 dark:text-sky-400/80" 
                 :class="{ 'animate-spin': !$reducedMotion }"
                 viewBox="0 0 100 100" 
                 fill="none" 
                 xmlns="http://www.w3.org/2000/svg">
                <!-- Circuit/Chip Design -->
                <rect x="25" y="25" width="50" height="50" 
                      stroke="currentColor" 
                      stroke-width="2" 
                      fill="none" 
                      rx="4"
                      :class="$reducedMotion ? 'animate-pulse' : ''"
                      style="stroke-dasharray: 200; stroke-dashoffset: 0;"
                      :style="!$reducedMotion ? 'animation: circuit-trace 2s linear infinite;' : ''"/>
                
                <!-- Internal connections -->
                <line x1="35" y1="25" x2="35" y2="15" stroke="currentColor" stroke-width="2"/>
                <line x1="50" y1="25" x2="50" y2="15" stroke="currentColor" stroke-width="2"/>
                <line x1="65" y1="25" x2="65" y2="15" stroke="currentColor" stroke-width="2"/>
                
                <line x1="35" y1="75" x2="35" y2="85" stroke="currentColor" stroke-width="2"/>
                <line x1="50" y1="75" x2="50" y2="85" stroke="currentColor" stroke-width="2"/>
                <line x1="65" y1="75" x2="65" y2="85" stroke="currentColor" stroke-width="2"/>
                
                <line x1="25" y1="35" x2="15" y2="35" stroke="currentColor" stroke-width="2"/>
                <line x1="25" y1="50" x2="15" y2="50" stroke="currentColor" stroke-width="2"/>
                <line x1="25" y1="65" x2="15" y2="65" stroke="currentColor" stroke-width="2"/>
                
                <line x1="75" y1="35" x2="85" y2="35" stroke="currentColor" stroke-width="2"/>
                <line x1="75" y1="50" x2="85" y2="50" stroke="currentColor" stroke-width="2"/>
                <line x1="75" y1="65" x2="85" y2="65" stroke="currentColor" stroke-width="2"/>
                
                <!-- Central processing unit -->
                <circle cx="50" cy="50" r="8" 
                        stroke="currentColor" 
                        stroke-width="2" 
                        fill="none"
                        :class="$reducedMotion ? 'animate-pulse' : ''"
                        :style="!$reducedMotion ? 'animation: core-pulse 1.5s ease-in-out infinite alternate;' : ''"/>
                
                <!-- Corner dots -->
                <circle cx="35" cy="35" r="2" fill="currentColor"/>
                <circle cx="65" cy="35" r="2" fill="currentColor"/>
                <circle cx="35" cy="65" r="2" fill="currentColor"/>
                <circle cx="65" cy="65" r="2" fill="currentColor"/>
            </svg>
        </div>
        
        {{-- Rotating Messages --}}
        <div class="space-y-3">
            <p class="text-lg font-medium text-neutral-800 dark:text-neutral-200 transition-opacity duration-300"
               x-text="currentMessage"
               :class="messageVisible ? 'opacity-100' : 'opacity-0'">
            </p>
            <div class="flex justify-center space-x-1">
                <div class="w-2 h-2 bg-sky-500/60 rounded-full animate-pulse"></div>
                <div class="w-2 h-2 bg-sky-500/40 rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
                <div class="w-2 h-2 bg-sky-500/20 rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
            </div>
        </div>
        
        {{-- Progress indicator --}}
        <div class="w-32 h-1 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden mx-auto">
            <div class="h-full bg-sky-500 rounded-full transition-all duration-100 ease-linear"
                 :style="`width: ${progress}%`"></div>
        </div>
    </div>
</div>

{{-- CSS Animations --}}
<style>
    @keyframes circuit-trace {
        0% { stroke-dashoffset: 200; }
        100% { stroke-dashoffset: 0; }
    }
    
    @keyframes core-pulse {
        0% { opacity: 0.5; transform: scale(1); }
        100% { opacity: 1; transform: scale(1.1); }
    }
</style>

{{-- Alpine.js Component --}}
<script>
    function loadingOverlay() {
        return {
            showOverlay: true,
            currentMessage: '',
            messageVisible: true,
            progress: 0,
            timeElapsed: 0,
            dataReady: false,
            messageIndex: 0,
            $reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            
            messages: @json(config('loading.messages', [
                'Bootstrapping kernel…',
                'Invoking Turing routine…',
                'Mounting /humility',
                'Linking Von Neumann unit…',
                'Paging Schrödinger\'s cache…',
                'Negotiating handshakes with HAL (be nice)',
                'Compiling manners'
            ])),
            
            init() {
                // Set initial message
                this.currentMessage = this.messages[0];
                
                // Start progress tracking
                this.startProgressTracking();
                
                // Start message rotation
                this.startMessageRotation();
                
                // Disable body scroll
                document.body.style.overflow = 'hidden';
                
                // Set focus trap
                this.$nextTick(() => {
                    this.$root.focus();
                });
            },
            
            startProgressTracking() {
                const minDuration = {{ config('loading.min_duration', 3000) }};
                const interval = setInterval(() => {
                    this.timeElapsed += 50;
                    this.progress = Math.min((this.timeElapsed / minDuration) * 100, 100);
                    
                    // Check if we can hide the overlay
                    if (this.timeElapsed >= minDuration && this.dataReady) {
                        this.hideOverlay();
                        clearInterval(interval);
                    }
                }, 50);
            },
            
            startMessageRotation() {
                const rotateMessage = () => {
                    if (this.showOverlay) {
                        this.messageVisible = false;
                        
                        setTimeout(() => {
                            this.messageIndex = (this.messageIndex + 1) % this.messages.length;
                            this.currentMessage = this.messages[this.messageIndex];
                            this.messageVisible = true;
                            
                            setTimeout(rotateMessage, {{ config('loading.message_interval', 900) }});
                        }, 150);
                    }
                };
                
                setTimeout(rotateMessage, {{ config('loading.message_interval', 900) }});
            },
            
            markReady() {
                this.dataReady = true;
                
                // If minimum time has passed, hide immediately
                const minDuration = {{ config('loading.min_duration', 3000) }};
                if (this.timeElapsed >= minDuration) {
                    this.hideOverlay();
                }
            },
            
            hideOverlay() {
                this.showOverlay = false;
                
                // Restore body scroll
                document.body.style.overflow = '';
                
                // Clear the session flag to prevent re-showing
                fetch('/clear-loading-flag', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }});
                
                // Focus on main content
                setTimeout(() => {
                    const mainHeading = document.querySelector('h1, [role="main"] h2, .dashboard-title');
                    if (mainHeading) {
                        mainHeading.focus();
                        mainHeading.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 300);
            }
        }
    }
</script>