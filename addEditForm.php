<?php

print 
'<form name="pridatUpravitZazanam" action="'. $_SERVER['PHP_SELF'].'" method="POST" class="pridatUpravitZazanam">
<div class="formNadpis" style="text-align: center;">';
if($upravitForm){
  print '<h4>Upravit kontakt</h4>';
}else{
 print '<h4>Přidat kontakt</h4>';
}

print '</div>

<div class="labelinput">
<label>Jméno:</label>
<input type="text" name="jmenoZkracenky" required value="'.$jmeno.'"></indput>
</div>

<div class="labelinput">
<label>Zkrácenka:</label>
<input type="text" name="cisloZkracenky" required value="'.$zkracena_volba.'"></indput>
</div>

<div class="labelinput">
<label>Telefon:</label>
<input type="text" name="telefon" required value="'.$telefonni_cislo.'"></indput>
</div>

<div class="labelinput">
<label>Místo:</label>
<input type="text" name="misto" value="'.$misto.'"></indput>
</div>

<div class="labelinput">
<label>Odbornost:</label>
<input type="text" name="odbornost" value="'.$odbornost.'"></indput>
</div>

<div class="labelinput">
<label>Město:</label>
<input type="text" name="mesto" value="'.$mesto.'"></indput>
</div>

<div class="labelinput">
<label>Zobrazit tel.</label>
<select name="zobrazitTel" id="zobrazitTel">
    <option value="ano">Ano</option>';
if($show_telefon_cislo){
   print '<option value="ne">Ne</option>';
}else{
    print '<option value="ne" selected>Ne</option>';
}
print '
</select>
</div>

<div class="formBtnSubmit">';


if($upravitForm){
    print '<input type="submit" value="Upravit"/>
    <input type=hidden name=mode value=edited> 
    <input type=hidden name=id value='.$id.'>';
}else{
print '<input type="submit" value="Vytvořit"/>
<input type=hidden name=mode value=added> ';
}
print '</div>

</form>';


?>