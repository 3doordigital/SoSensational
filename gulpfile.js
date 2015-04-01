var gulp = require('gulp');
var sass = require('gulp-sass');
var watch = require('gulp-watch');
var minifyCSS = require('gulp-minify-css');

gulp.task('sass', function() {
    gulp.src('plugins/SoSensational/styles/sass/*.scss')
            .pipe(sass())
            .pipe(gulp.dest('plugins/SoSensational/styles/dest/'));
});

gulp.task('minify-css', function() {
    gulp.src('plugins/SoSensational/styles/dest/*.css')
            .pipe(minifyCSS())
            .pipe(gulp.dest('plugins/SoSensational/styles/dest/'));
});

gulp.task('watch', function() {
    gulp.watch('plugins/SoSensational/styles/sass/*.scss', ['sass', 'minify-css']);
});