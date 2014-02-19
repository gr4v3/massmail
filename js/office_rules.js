/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function rnd(minv, maxv){
	if (maxv < minv) return 0;
	return Math.floor(Math.random()*(maxv-minv+1)) + minv;
}
function getName(minlength, maxlength, prefix, suffix)
{
	prefix = prefix || '';
	suffix = suffix || '';
	//these weird character sets are intended to cope with the nature of English (e.g. char 'x' pops up less frequently than char 's')
	//note: 'h' appears as consonants and vocals
	var vocals = 'aeiouyh' + 'aeiou' + 'aeiou' + 'aeiou';
	var cons = 'bcdfghjklmnpqrstvwxz' + 'bcdfgjklmnprstvw' + 'bcdfgjklmnprst' + 'bcdfgjklmnprst';
	var allchars = vocals + cons;
	//minlength += prefix.length;
	//maxlength -= suffix.length;
        maxlength = 14;
	var length = rnd(minlength, maxlength) - prefix.length - suffix.length;
	if (length < 1) length = 1;
	//alert(minlength + ' ' + maxlength + ' ' + length);
	var consnum = 0;
	//alert(prefix);
	/*if ((prefix.length > 1) && (cons.indexOf(prefix[0]) != -1) && (cons.indexOf(prefix[1]) != -1)) {
		//alert('a');
		consnum = 2;
	}*/
	if (prefix.length > 0) {
		for (var i = 0; i < prefix.length; i++) {
			if (consnum == 2) consnum = 0;
			if (cons.indexOf(prefix[i]) != -1) {
				consnum++;
			}
		}
	}
	else {
		consnum = 1;
	}

	var name = prefix;

	for (var i = 0; i < length; i++)
	{
		//if we have used 2 consonants, the next char must be vocal.
		if (consnum == 2)
		{
			touse = vocals;
			consnum = 0;
		}
		else touse = allchars;
		//pick a random character from the set we are goin to use.
		c = touse.charAt(rnd(0, touse.length - 1));
		name = name + c;
		if (cons.indexOf(c) != -1) consnum++;
	}
	name = name.charAt(0) + name.substring(1, name.length) + suffix;
	return name;
}
function getRandomMailSubDomain() {
	var subdomains = [
		'@mail.com',
		'@email.com',
		'@usa.com',
		'@consultant.com',
		'@myself.com',
		'@london.com',
		'@europe.com',
		'@post.com',
		'@dr.com',
		'@doctor.com',
		'@lawyer.com',
		'@engineer.com',
		'@techie.com',
		'@linuxmail.org',
		'@iname.com',
		'@cheerful.com',
		'@contractor.net',
		'@accountant.com',
		'@asia.com',
		'@writeme.com',
		'@uymail.com'
	];
	var subdomains_length = subdomains.length;
	var random_index = rnd(0,subdomains_length - 1);
	return subdomains[random_index];
}
function getRandomFirstName() {
	var firstname = new Array('Jean','Baptiste','Joseph','Pierre','Francois','Louis','Antoine','Charles','Michel','Jacques','Augustin','Joseph','Marie','Jean','Etienne','Alexis','Andre','Nicolas','Jean','Paul','Xavier','Ignace','Gabriel','Amable','Toussaint','Guillaume','Louise','Anne','Marguerite','Madeleine','Angelique','Genevieve','Catherine','Elisabeth','Angelique','Charlotte','antonio','fabio','Mimi','Sara','Joao','Carlos','Mariana','Maria','emanuel','tiago','fabiana','cecile','Lea','Manon','Emma','Chloe','Camille','Oceane','Clara','Sarah','Ines','Marie','Lucie','Anais','Julie','Laura','Mathilde','Pauline','Lisa','Eva','Jade','Marine','Maeva','Justine','Juliette','Charlotte','Celia','Emilie','Lola','Louise','Amandine','Noemie','Elisa','Clemence','Romane','Margaux','Morgane','Marion','Zoe','Jeanne','Melissa','Alicia','Alice','Margot','Elise','Amelie','Carla','Lou','Lena','Ambre','Flavie','Laurine','Agathe','Maelle','Coralie','Elodie','Anna','Fanny','Solene','Alexia','Audrey','Elsa','Valentine','Cassandra','Melanie','Claire','Lilou','Eloise','Axelle','Julia','Emeline','Nina','Salome','Maelys','Andrea','Lina','Ilona','Estelle','Coline','Anaelle','Victoria','Clarisse','Nolwenn','Clementine','Myriam','Yasmine','Laurie','Jessica','Alexandra','Caroline','Sophie','Leonie','Melina','Lise','Olivia','Louna','Lucile','Marina','Celine','Cloe','Aurelie','Luna','Lucas','Theo','Hugo','Thomas','Enzo','Maxime','Alexandre','Mathis','Nathan','Antoine','Clement','Romain','Alexis','Louis','Quentin','Leo','Nicolas','Tom','Baptiste','Paul','Valentin','Arthur','Axel','Julien','Matteo','Matheo','Benjamin','Jules','Pierre','Yanis','Florian','Dylan','Raphael','Adrien','Corentin','Kevin','Mathieu','Maxence','Anthony','Victor','Noah','Gabriel','Killian','Simon','Guillaume','Dorian','Samuel','Bastien','Mohamed','Tristan','Vincent','Rayan','Jeremy','Martin','Kylian','Evan','Thibault','Ethan','Mateo','Remi');
	var firstname_length = firstname.length;
	var random_index = rnd(0,firstname_length - 1);
	return firstname[random_index];
}
function getRandomLastName() {
	var lastname  = new Array('Roy','Gauthier','Gagnon','Lefebvre','Morin','Boucher','Langlois','Renaud','Fournier','Caron','Thibault','Demers','Girard','Giroux','Tessier','Robert','Beaudouin','Archambault','Dubois','Ouellet','Langlois','Gagne');
	var lastname_length = lastname.length;
	var random_index = rnd(0,lastname_length - 1);
	return lastname[random_index];
}
function getTimestamp() {
	return new Date().valueOf();
}


var Rules = new Hash({
	
	'localhost':{
		'pass':'Dofasol123',
		'email':getName(10,12)+'@pantors.info',
		'login':'@pantors.info',
		'url':'https://eu.edit.yahoo.com/registration?intl=uk',
		'loginurl':'https://eu.edit.yahoo.com',
		refresh:function(){
			var name = getName(10,12);
			this.email = name +'@pantors.info';
			this.login = name;
		}
	},
	

	'laposte':{
		'pass':'Dofasol123',
		'email':getName(10,12)+'@laposte.net',
		'login':'@sapo.pt',
		'url':'https://compte.laposte.net/inscription/etape1.do',
		'loginurl':'http://www.laposte.net/',
		refresh:function(){
			var name = getName(10,12);
			this.email = name +'@laposte.net';
			this.login = name;
		}
	},


	'sapo':{
		'pass':'Dofasol123',
		'email':getName(10,12)+'@sapo.pt',
		'login':'@sapo.pt',
		'url':'https://registo.mail.sapo.pt/',
		'loginurl':'http://mail.sapo.pt',
		refresh:function(){
			var name = getName(10,12);
			this.email = name +'@sapo.pt';
			this.login = name;
		}
	},

    'gmx':{
		'pass':'Dofasol123',
		'email':getName(10,12)+'@gmx.com',
		'login':'@gmx.com',
		'url':'http://www.gmx.com/registration.html',
		'loginurl':'http://www.gmx.com/',
		refresh:function(){
			var name = getName(10,12);
			this.email = name +'@gmx.com';
			this.login = name;
		}
	},
	
	'mail':{
		'pass':'Lafasol123',
		'email':false,
		'login':'@mail.com',
		'url':'http://service.mail.com/registration.html',
		'loginurl':'http://www.mail.com/int/',
		refresh:function(){
			var timestamp = getTimestamp();
			var fisrtname = getRandomFirstName();
			var lastname  = getRandomLastName();
			this.email = fisrtname + timestamp + getRandomMailSubDomain();
			this.login = fisrtname + timestamp;
			this.firstname = fisrtname;
			this.lastname = lastname;
		}
	},

	'gmail':{
		'pass':'asd123',
		'email':getName(10,12)+'@gmail.com',
		'login':'@gmail.com',
		refresh:function(){
			this.email = getName(10,12)+'@gmail.com';
			this.login = this.email;
		}
	},

	'yahoo com':{
		'pass':'Dofasol123',
		'email':'',
		'login':'',
		'url':'https://edit.yahoo.com/registration?.intl=us&.pd=ym_ver%253D0%2526c%253D%2526ivt%253D%2526sg%253D&new=1&.done=http%3A//us.mg5.mail.yahoo.com/dc/launch%3F.rand=1283360655414%26&.src=ym&.v=0&.u=4hfku3567t1tj&partner=&.partner=&pkg=&stepid=&.p=&promo=&.last=',
		refresh:function(){
			var name = getName(10,12);
			this.email = name+'@yahoo.com';
			this.login = name;
		}
	},

	'yahoo fr':{
		'pass':'Dofasol123',
		'email':'',
		'login':'',
		'url':'https://eu.edit.yahoo.com/registration?intl=fr',
		'loginurl':'http://fr.mail.yahoo.com',
		refresh:function(){
			var name = getName(10,12);
			this.email = name+'@yahoo.fr';
			this.login = name;
		}
	},

	'yahoo uk':{
		'pass':'Dofasol123',
		'email':'',
		'login':'',
		'url':'https://eu.edit.yahoo.com/registration?intl=uk',
		'loginurl':'http://uk.mail.yahoo.com',
		'firstname':'',
		'lastname':'',
		refresh:function(){
			//var name = getName(10,12);
			var timestamp = getTimestamp();
			var fisrtname = getRandomFirstName();
			var lastname  = getRandomLastName();
			this.email = fisrtname + timestamp + '@yahoo.co.uk';
			this.login = fisrtname + timestamp;
			this.firstname = fisrtname;
			this.lastname = lastname;
		}
	},

	'yahoo au':{
		'pass':'Dofasol123',
		'email':'',
		'login':'',
		'url':'https://eu.edit.yahoo.com/registration?intl=au',
		'loginurl':'http://au.mail.yahoo.com',
		refresh:function(){
			var name = getName(10,12);
			this.email = name+'@yahoo.com.au';
			this.login = name;
		}
	},

    'yahoo ie':{
		'pass':'Dofasol123',
		'email':'',
		'login':'',
		'url':'https://eu.edit.yahoo.com/registration?intl=ie',
		'loginurl':'http://ie.mail.yahoo.com',
		refresh:function(){
			var name = getName(10,12);
			this.email = name+'@yahoo.ie';
			this.login = name;
		}
	},

	'yahoo it':{
		'pass':'Dofasol123',
		'email':'',
		'login':'',
		'url':'https://eu.edit.yahoo.com/registration?intl=it',
		'loginurl':'http://it.yahoo.com',
		refresh:function(){
			var name = getName(10,12);
			this.email = name+'@yahoo.it';
			this.login = name;
		}
	},

	'yahoo es':{
		'pass':'Dofasol123',
		'email':'',
		'login':'',
		'url':'https://eu.edit.yahoo.com/registration?intl=es&origIntl=es',
		'loginurl':'http://es.yahoo.com',
		refresh:function(){
			var name = getName(10,12);
			this.email = name+'@yahoo.es';
			this.login = name;
		}
	},

	'yahoo de':{
		'pass':'Dofasol123',
		'email':'',
		'login':'',
		'url':'https://eu.edit.yahoo.com/registration?intl=de&origIntl=',
		'loginurl':'http://es.yahoo.com',
		refresh:function(){
			var name = getName(10,12);
			this.email = name+'@yahoo.de';
			this.login = name;
		}
	},

    'yahoo':{
		'pass':'Dofasol123',
		'email':'',
		'login':'',
		'url':'https://na.edit.yahoo.com/registration?.pd=&intl=us&origIntl=&done=&src=&last=&partner=yahoo_default&domain=&yahooid=',
		'loginurl':'https://na.edit.yahoo.com/',
		refresh:function(){
			var name = getName(10,12);
			this.email = name+'@yahoo.com';
			this.login = name;
		}
	},


	'hotmail':{
		'pass':'Dofasol123',
		'email':'',
		'login':'',
		'url':'https://signup.live.com',
		'loginurl':'http://login.live.com',
		refresh:function(){
			var name = getName(10,12);
			this.email = name+'@hotmail.com';
			this.login = name;
		}
	},

	'aol':{
		'pass':'Dofasol123',
		'email':'',
		'login':'',
		refresh:function(){
			
		}
	},

	'gawab':{
		'pass':'Dofasol123',
		'email':'',
		'login':'',
		refresh:function(){
			var name = getName(10,12);
			this.email = name+'@gawab.com';
			this.login = this.email;
		}
	},

	'lavabit':{
		'pass':'Dofasol123',
		'email':'',
		'login':'',
		'url':'https://lavabit.com/apps/register',
		refresh:function(){
			var name = getName(10,12);
			this.email = name+'@lavabit.com';
			this.login = name;
		}
	},

	'vfemail':{
		'pass':'',
		'email':'',
		'login':'',
		refresh:function(){
			var name = getName(10,12);
			this.email = name+'@vfemail.net';
			this.login = this.email;
		}
	},

	'aim':{
		'pass':'Dofasol123',
		'email':'',
		'login':'',
		'url':'https://my.screenname.aol.com/_cqr/login/login.psp?sitedomain=sns.webmail.aol.com&lang=en&seamless=novl&offerId=newmail-en-us-v2&authLev=0&siteState=ver%3A4|rt%3ASTANDARD|at%3ASNS|ld%3Awebmail.aol.com|uv%3AAOL|lc%3Aen-us|mt%3AAOL|snt%3AScreenName|sid%3A7a3059a2-1602-40b1-b020-20428001b72e&locale=us',
		'loginurl':'https://my.screenname.aol.com/_cqr/login/login.psp?sitedomain=sns.webmail.aol.com&lang=en&seamless=novl&offerId=newmail-en-us-v2&authLev=0&siteState=ver%3A4|rt%3ASTANDARD|at%3ASNS|ld%3Awebmail.aol.com|uv%3AAOL|lc%3Aen-us|mt%3AAOL|snt%3AScreenName|sid%3A7a3059a2-1602-40b1-b020-20428001b72e&locale=us',
		refresh:function(){
			var name = getName(10,12);
			this.email = name+'@aim.com';
			this.login = name;
		}
	}
});