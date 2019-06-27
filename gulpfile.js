const gulp = require('gulp');
const htmlmin = require('gulp-htmlmin');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const pathsToMove = [
    'assets/css/lib/*.css',
    'assets/fonts/*.*',
    'assets/img/*.*',
    'assets/js/*.*'
];
gulp.task('css', () => {
    let plugins = [
        autoprefixer({browsers: ['last 2 versions']}),
        cssnano()
    ];
    return gulp.src('assets/css/style.css')
        .pipe(postcss(plugins))
        .pipe(gulp.dest('build/assets/css'));
});
gulp.task('minifyIndex', () => {
    return gulp.src('*.php')
        .pipe(htmlmin({collapseWhitespace: true}))
        .pipe(gulp.dest('build'))
});
gulp.task('build', () => {
    return gulp.src(pathsToMove, {base: 'assets/'})
        .pipe(gulp.dest('build/assets'))
});


gulp.task('default', ['css', 'minifyIndex', 'build']);
