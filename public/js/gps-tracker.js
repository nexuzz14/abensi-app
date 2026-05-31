/**
 * gps-tracker.js
 * GPS Tracker untuk Sistem Absensi
 * Menggunakan HTML5 Geolocation API dengan akurasi tinggi
 */

class GpsTracker {
    constructor() {
        this.latitude  = null;
        this.longitude = null;
        this.accuracy  = null;
        this.status    = 'idle'; // 'idle', 'loading', 'connected', 'error'
        this.callbacks = {
            onSuccess: null,
            onError:   null,
            onUpdate:  null,
        };
    }

    /**
     * Mulai tracking GPS
     * @param {object} callbacks - { onSuccess, onError, onUpdate }
     */
    start(callbacks = {}) {
        this.callbacks = { ...this.callbacks, ...callbacks };

        if (!navigator.geolocation) {
            this.status = 'error';
            this.callbacks.onError?.('Browser tidak mendukung Geolocation API');
            return;
        }

        this.status = 'loading';

        navigator.geolocation.getCurrentPosition(
            (position) => this._onSuccess(position),
            (error)    => this._onError(error),
            {
                enableHighAccuracy: true,  // Gunakan GPS hardware (lebih akurat)
                timeout:            10000, // Timeout 10 detik
                maximumAge:         0,     // Selalu ambil posisi terbaru (tidak dari cache)
            }
        );
    }

    /**
     * Handler sukses mendapatkan GPS
     * @private
     */
    _onSuccess(position) {
        this.latitude  = position.coords.latitude;
        this.longitude = position.coords.longitude;
        this.accuracy  = position.coords.accuracy;
        this.status    = 'connected';

        this.callbacks.onSuccess?.({
            latitude:  this.latitude,
            longitude: this.longitude,
            accuracy:  this.accuracy,
        });
    }

    /**
     * Handler error GPS
     * @private
     */
    _onError(error) {
        this.status = 'error';

        const pesanError = {
            1: 'Izin GPS ditolak. Aktifkan izin lokasi di browser.',
            2: 'GPS tidak tersedia. Pindah ke area dengan sinyal lebih baik.',
            3: 'GPS timeout. Coba lagi.',
        };

        const pesan = pesanError[error.code] || `Error GPS: ${error.message}`;
        this.callbacks.onError?.(pesan);
    }

    /**
     * Cek apakah akurasi GPS memenuhi syarat
     * @param {number} maxAccuracy - Akurasi maksimum dalam meter
     * @returns {boolean}
     */
    isAccurate(maxAccuracy = 100) {
        return this.accuracy !== null && this.accuracy <= maxAccuracy;
    }

    /**
     * Dapatkan data GPS saat ini
     * @returns {object|null}
     */
    getData() {
        if (this.status !== 'connected') return null;

        return {
            latitude:  this.latitude,
            longitude: this.longitude,
            accuracy:  this.accuracy,
        };
    }

    /**
     * Format akurasi untuk tampilan UI
     * @returns {string}
     */
    getAccuracyLabel() {
        if (this.accuracy === null) return '—';

        if (this.accuracy <= 20)  return `±${Math.round(this.accuracy)}m 🟢 Sangat Baik`;
        if (this.accuracy <= 50)  return `±${Math.round(this.accuracy)}m 🟡 Baik`;
        if (this.accuracy <= 100) return `±${Math.round(this.accuracy)}m 🟠 Cukup`;
        return `±${Math.round(this.accuracy)}m 🔴 Buruk`;
    }
}

// Export sebagai singleton
window.GpsTracker = GpsTracker;
