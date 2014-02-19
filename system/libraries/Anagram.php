<?php

# anagram
# coded by Alessandro Rosa
# e-mail : zandor_zz@yahoo.it
# site : http://malilla.supereva.it

# Copyright (C) 2006  Alessandro Rosa

# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

# Compiled with PHP 4.4.0


class CI_Anagram
{

  function  __construct() 
  {
      $this->reset() ;
  } 

  function reset()
  {
      $this->counter = 0 ;
      $this->bSaveFile = false ;
      $this->bInitialLetters = false ;
      $this->bTerminalLetters = false ;
      $this->bOne_bond = false ;
      
      $this->max_bonds = 0;
      unset( $this->bonds ) ;
  } 

  function set_save_file( $bSF )        { $this->bSaveFile = $bSF ;   } 
  function set_save_file_name( $name )  { $this->save_file_name = $name ;   } 

  function add_bond( $letter_position )
  {
      $cnt = count( $this->bonds );
      
      $this->bonds[$cnt] = $letter_position - 1 ;
      $this->max_bonds = count( $this->bonds );
      
      $this->bOne_bond = true ;
  } 

  function insert_initials( $l )
  {
      $this->initial_letters = $l ;
      $this->initial_letters_len = strlen( $this->initial_letters );
      $this->bInitialLetters = true ;
  } 

  function insert_terminals( $l )
  {
      $this->terminal_letters = $l ;
      $this->terminal_letters_len = strlen( $this->terminal_letters );
      $this->bTerminalLetters = true ;
  } 
  
  function insert_word( $in )
  {
      $this->original_word = $in ;
  
      // creation of flags string

      $this->original_word_length = strlen( $in ) ;
      
      $this->flags_string = "";
      
      for ( $i = 0 ; $i < $this->original_word_length ; $i++ )
          $this->flags_string .= "N" ;
  } 

  function go()
  {
      if ( !isset( $this->original_word ) )
      {
          echo "<br/>No anagram can be performed.<br/>The input word was not initialized !<br/>" ;
          return ; 
      } 
      else if ( strlen( $this->original_word ) == 0 )
      {
          echo "<br/>No anagram can be performed.<br/>The input word is empty !<br/>" ;
          return ; 
      } 
      
      set_time_limit( 0 ) ;
      
      if ( strlen( $this->save_file_name ) == 0 ) $this->save_file_name = "list.html" ; 
  
      if ( $this->bSaveFile ) $this->FileHandle = fopen( $this->save_file_name, "w+" );

      $this->counter = 0 ;
      $this->permute( $this->flags_string, "", 0 );

      if ( $this->bSaveFile ) fclose( $this->FileHandle );

      $this->max_bonds = 0;
      unset( $this->bonds ) ;
  } 


  function display( $word )
  {
      if ( $this->bInitialLetters and $this->bTerminalLetters )
      {
          $initials = substr( $word, 0, $this->initial_letters_len );
          $terminals = substr( $word, -$this->terminal_letters_len, $this->terminal_letters_len );

          if ( strcmp( $initials, $this->initial_letters ) == 0 and strcmp( $terminals, $this->terminal_letters ) == 0 )
          {
              $this->counter++ ;
                  
              $outstring = "<font face=\"arial\" size=\"2\">$this->counter) <b>$word</b></font><br/>\r\n" ;
                  
              echo $outstring ;
                    
              if ( $this->bSaveFile ) fwrite( $this->FileHandle, $outstring ) ;
          } 
      } 
      else if ( $this->bInitialLetters )
      {
          $initials = substr( $word, 0, $this->initial_letters_len );
          if ( !strcmp( $initials, $this->initial_letters ) == 0 ) return ;
          
          $this->counter++ ;
              
          $outstring = "<font face=\"arial\" size=\"2\">$this->counter) <b>$word</b></font><br/>\r\n" ;
          echo $outstring ;
              
          if ( $this->bSaveFile ) fwrite( $this->FileHandle, $outstring ) ;
      } 
      else if ( $this->bTerminalLetters )
      {
          $terminals = substr( $word, -$this->terminal_letters_len, $this->terminal_letters_len );
          if ( !strcmp( $terminals, $this->terminal_letters ) == 0 ) return ;

          $this->counter++ ;
              
          $outstring = "<font face=\"arial\" size=\"2\">$this->counter) <b>$word</b></font><br/>\r\n" ;
              
          echo $outstring ;
              
          if ( $this->bSaveFile ) fwrite( $this->FileHandle, $outstring ) ;
      } 
      else
      {
          $this->counter++ ;
              
          $outstring = "<font face=\"arial\" size=\"2\">$this->counter) <b>$word</b></font><br/>\r\n" ;
              
          echo $outstring ;
              
          if ( $this->bSaveFile ) fwrite( $this->FileHandle, $outstring ) ;
      }

  } 

  function permute( $tmp, $word, $level )
  {
      if ( strlen( $word ) == $this->original_word_length )
      {
          $this->display( $word );
          return ;
      } 

      for ( $i = 0 ; $i < $this->original_word_length ; $i++ )
      {
          if ( $this->bOne_bond == true and $this->bonds[$level] != -1 and $i != $this->bonds[$level] and $level < $this->max_bonds ) continue ;
          
              if ( strcmp( $tmp{$i}, "N" ) == 0 )
              {
                  $word_tmp = $word ;
                  $word_tmp .= $this->original_word{$i};
                  
                  $flags_tmp = $tmp ;
                  $flags_tmp{$i} = "Y";
                  
                  $this->permute( $flags_tmp, $word_tmp, $level+1 );
              }
      } 
  } 

  var $counter ;
  var $original_word ;
  var $original_word_length ;
  var $flags_string ;

  var $initial_letters ;
  var $initial_letters_len;
  var $bInitialLetters ;

  var $one_bond ;
  var $bOne_bond ;

  var $terminal_letters ;
  var $terminal_letters_len;
  var $bTerminalLetters ;

  var $bonds ;
  var $max_bonds ;

  var $bSaveFile ;
  var $save_file_name ;

  var $FileHandle ;
}

?>
