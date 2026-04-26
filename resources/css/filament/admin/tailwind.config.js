import preset from '../../../../vendor/filament/filament/tailwind.config.preset';

export default {
    presets: [preset], // 🔥 هنا تستورد ثيم Filament الأصلي بالكامل
    content: [
        '../../../../vendor/filament/**/*.blade.php',
        '../../../../vendor/filament/**/*.js',
        '../../../../resources/views/**/*.blade.php',
        '../../../../app/Filament/**/*.php',
        '../../../../app/Livewire/**/*.php',
    ],
};
