var gulp = require('gulp');
var sass = require('gulp-sass');
var watch = require('gulp-watch');
var minifyCSS = require('gulp-minify-css');
var rename = require('gulp-rename');

gulp.task('sass', function() {
    gulp.src('plugins/SoSensational/styles/sass/*.scss')
            .pipe(sass({
                sourceComments: 'map',
                sourceMap: 'sass',
                outputStyle: 'nested'
            }))
            .pipe(gulp.dest('plugins/SoSensational/styles/dist/'));
});

gulp.task('minify-css', ['sass'], function() {
    gulp.src('plugins/SoSensational/styles/dest/*.css')
            .pipe(minifyCSS())
            .pipe(gulp.dest('plugins/SoSensational/styles/dist/min'));
});

gulp.task('rename', ['minify-css'], function() {
    gulp.src('plugins/SoSensational/styles/dist/min')
            .pipe(rename(function(path) {
                path.basename += ".min";
            }))
            .pipe(gulp.dest("./"));
});

gulp.task('watch', function() {
    gulp.watch('plugins/SoSensational/styles/sass/*.scss', ['sass', 'minify-css']);
});