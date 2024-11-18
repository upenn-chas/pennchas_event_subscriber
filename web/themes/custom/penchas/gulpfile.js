import gulp from 'gulp';
import autoprefixer from 'gulp-autoprefixer';
import cleanCSS from 'gulp-clean-css';
import gulpSass from 'gulp-sass';
import sourcemaps from 'gulp-sourcemaps';
import * as sass from 'sass'; // Updated import syntax for Sass

// Initialize gulp-sass with Dart Sass
const sassCompiler = gulpSass(sass);

// Paths
const paths = {
  scss: {
    src: 'src/scss/**/*.scss',
    dest: 'dist/css'
  }
};

// Compile SCSS
export function compileSCSS() {
  return gulp.src(paths.scss.src)
    .pipe(sourcemaps.init())
    .pipe(sassCompiler().on('error', sassCompiler.logError))  // Use the new sassCompiler
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(cleanCSS())
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(paths.scss.dest));
}

// Watch Files for Changes
export function watchFiles() {
  gulp.watch(paths.scss.src, compileSCSS);
}

// Define the default task
export const defaultTask = gulp.series(compileSCSS, watchFiles);

// Define the 'default' task so Gulp runs it when you type 'gulp' or 'npm run gulp'
export default defaultTask;
