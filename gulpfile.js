'use strict';

var gulp	= require('gulp'),
scss		= require('gulp-sass'),
concat		= require('gulp-concat'),
cssnano		= require('gulp-cssnano'),
sourcemaps 	= require('gulp-sourcemaps'),
supported 	= ['last 2 versions','safari >= 8','ie >= 9','ff >= 20','ios 6','android 4'],
files 		= {
	js: ['assets/js/*.js'],
	scss: ['assets/scss/*.scss']
};

gulp.task('compile scss, minify styles', function(){
	return gulp.src(files.scss)
				.pipe(sourcemaps.init({
					autoprefixer: {browsers: supported, add: true}
				}))
				.pipe(scss())
				.pipe(cssnano())
				.pipe(sourcemaps.write())
				.pipe(gulp.dest('./'));
});

gulp.task('concatenate scripts', function() {
	return gulp.src(files.js)
				.pipe(sourcemaps.init())
				.pipe(concat('scripts.js'))
				.pipe(sourcemaps.write())
				.pipe(gulp.dest('./'));
});

gulp.task('default', function(){
	//gulp.watch(files.js, gulp.series('concatenate scripts'));
	gulp.watch(files.scss, gulp.series('compile scss, minify styles'));
});
