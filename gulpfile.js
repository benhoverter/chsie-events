/*
 *  Gulp config file
 *  Project: chsie_events
 *  Author: Ben Hoverter
 */

var gulp = require( 'gulp' );
var sass = require( 'gulp-sass' );

gulp.task( 'watch', function(){
    gulp.watch( 'admin/**/*.sass', ['sass-admin'] );
    gulp.watch( 'public/**/*.sass', ['sass-public'] );
    // Other watchers here
} );

gulp.task( 'build', function(){

} );


gulp.task( 'sass-admin', function(){
    return gulp.src( 'admin/sass/chsie-events-admin.sass' )
    .pipe( sass() )
    .pipe( gulp.dest( 'admin/css' ) )
} );

gulp.task( 'sass-public', function(){
    return gulp.src( 'public/sass/chsie-events-public.sass' )
    .pipe( sass() )
    .pipe( gulp.dest( 'public/css' ) )
} );
