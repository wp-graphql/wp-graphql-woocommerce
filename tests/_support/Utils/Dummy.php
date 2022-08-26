<?php
/**
 * Simple dummy text generator
 *
 * @author @morningtoast, @kidunot89
 * @link https://github.com/morningtoast/dummy
 * @since 0.8.0
 * @package Tests\WPGraphQL\Utils
 */

namespace Tests\WPGraphQL\WooCommerce\Utils;

/**
 * Class - Dummy
 */
final class Dummy {
	/**
	 * Stores the instance of the Dummy class
	 *
	 * @var Dummy The one true dummy =P
	 */
	private static $instance;

	/**
	 * Returns the dummy =P.
	 *
	 * @return Dummy
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( is_a( self::$instance, __CLASS__ ) ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function all() {
		$un = $this->username();
		$fn = $this->firstname();
		$ln = $this->lastname();

		$dump = [
			'primaryText'   => $this->words( 6 ),
			'secondaryText' => $this->sentence(),
			'userid'        => $this->number(),
			'rid'           => $this->number(),

			'link'          => 'http://www.google.com',
			'date'          => $this->mdy(),
			'time'          => $this->hm(),
			'text'          => $this->text( 40, 140 ),
			'body'          => $this->text( 140, 600 ),
			'number'        => $this->number(),
			'background'    => $this->image( 640, 480 ),
			'image'         => $this->image(),
			'thumbnail'     => $this->placeholder( 150, 150 ),
			'price'         => $this->price(),
			'product'       => $this->product(),
			'firstname'     => $fn,
			'lastname'      => $ln,
			'name'          => $fn . ' ' . $ln,
			'username'      => $un,
			'email'         => $this->email( $un ),
			'street'        => $this->street(),
			'city'          => $this->city(),
			'state'         => $this->state(),
			'zipcode'       => $this->zipcode(),
			'telephone'     => $this->telephone(),
		];

		return( $dump );
	}

	public function dump( $qty = 5 ) {
		$a = [];

		for ( $b = 0;$b < $qty;$b++ ) {
			$a[] = $this->all();
		}

		return( $a );
	}

	public function yesno( $w = 5 ) {
		if ( rand( 0, 10 ) > $w ) {
			return( true );
		} else {
			return( false );
		}
	}

	public function mdy() {
		return( rand( 1, 12 ) . '/' . rand( 1, 30 ) . '/' . rand( 1943, 2010 ) );
	}

	public function hm( $s = false ) {
		$t = rand( 1, 12 ) . ':' . rand( 10, 59 );

		if ( $s ) {
			$t = $t . ':' . rand( 10, 59 ); }

		if ( $this->yesno() ) {
			$t .= ' AM';
		} else {
			$t .= ' PM'; }

		return( $t );
	}

	public function text( $min = 30, $max = 120, $nospaces = false ) {
		$s = 'Aliquam lectus nulla, eleifend ut tellus in, euismod porttitor arcu. Aliquam erat volutpat. Morbi massa sapien, condimentum ultrices pretium porta, semper ac lacus. Nulla pharetra, urna a lacinia facilisis, neque libero placerat turpis, vel tincidunt massa nunc ac nunc. In nec volutpat ligula. Sed hendrerit ligula vel felis venenatis egestas. Duis sit amet pharetra erat. Interdum et malesuada fames ac ante ipsum primis in faucibus. Morbi rutrum vel magna et tincidunt. Curabitur mattis, sem eget suscipit porta, nibh enim auctor mi, eu lacinia magna velit eu quam. Donec commodo ultrices lorem vel ultrices. Aliquam erat volutpat. Praesent in libero bibendum, euismod dui quis, volutpat purus. Fusce non est egestas, volutpat ipsum ut, cursus augue. In hac habitasse platea dictumst. Mum volutpat.';
		$s = explode( ' ', $s );
		shuffle( $s );
		$s = implode( ' ', $s );
		$s = ucfirst( substr( $s, 0, rand( $min, $max ) ) );

		if ( $nospaces ) {
			$s = str_replace( ' ', '', $s );
		}

		return( $s );
	}

	public function words( $max = 5, $cmin = 2, $cmax = 6 ) {
		$t = [];

		for ( $a = 0; $a <= ( $max - 1 ); $a++ ) {
			$t[] = str_replace( [ ',', '.' ], [ '', '' ], $this->text( $cmin, ( $cmax + 2 ), true ) );
		}

		return( implode( ' ', $t ) );
	}

	public function sentence( $min = 3, $max = 10 ) {
		$l = rand( $min, $max );

		$w = ucfirst( strtolower( $this->words( $l ) ) ) . '.';
		return( $w );
	}

	public function number( $min = 10, $max = 1000 ) {
		return( round( rand( $min, $max ) ) );
	}

	public function image( $w = 300, $h = 300 ) {
		$c = [ 'city', 'people', 'animals', 'food', 'business', 'cats', 'technics', 'sports' ];
		shuffle( $c );
		$u = 'http://lorempixel.com/' . $w . '/' . $h . '/' . current( $c );

		return( $u );
	}

	public function placeholder( $w = 300, $h = 300 ) {
		return( 'http://placehold.it/' . $w . 'x' . $h );
	}

	public function price( $min = 2, $max = 500 ) {
		$c = [ '00', '99', '49', '00', '50', '29' ];
		shuffle( $c );
		$p = rand( $min, $max ) . '.' . current( $c );
		return( $p );
	}

	public function product() {
		$items = 'book,exam,hub cap,paperclip,model train,phaser,socks,light saber,lamp,piano,salad,lunch,violin,pony,deoderant,powder,radio,purse,mp3 player,eyeliner,laptop,wheel,glove,boot,pen,slippers,bed,dog,wall,paper,cellular phone,clock,tree,guitar,mullet,dog,cushion,table,ginger,roundabout,cake,teapot,dreadlocks,lampshade,piggy bank,church,eagle,butterfly,keyboard,laptop';
		$adj1  = 'original,brand new,mint,new-in-box,trendy,vintage,retro,old,new,gently used,big,small,tarnished,broken,refurbished';
		$adj2  = 'hipster,red,blue,green,orange,muddy,waterproof,purple,stainless steel,24k gold,handmade,custom';

		$a  = explode( ',', $adj1 );
		$a2 = explode( ',', $adj2 );
		$i  = explode( ',', $items );
		shuffle( $a );
		shuffle( $a2 );
		shuffle( $i );

		// $t = ucfirst($a[0]." ".$a2[3]." ".$i[1]);
		$t = ucfirst( $a2[3] . ' ' . $i[1] );
		return( $t );
	}

	public function firstname( $gender = 'any' ) {
		$m   = explode( ',', 'James,John,Robert,Michael,William,David,Richard,Charles,Joseph,Thomas,Christopher,Daniel,Paul,Mark,Donald,George,Kenneth,Steven,Edward,Brian,Ronald,Anthony,Kevin,Jason,Matthew,Gary,Timothy,Jose,Larry,Jeffrey,Frank,Scott,Eric,Stephen,Andrew,Raymond,Gregory,Joshua,Jerry,Dennis,Walter,Patrick,Peter,Harold,Douglas,Henry,Carl,Arthur,Ryan,Roger' );
		$fm  = explode( ',', 'Mary,Patricia,Linda,Barbara,Elizabeth,Jennifer,Maria,Susan,Margaret,Dorothy,Lisa,Nancy,Karen,Betty,Helen,Sandra,Donna,Carol,Ruth,Sharon,Michelle,Laura,Sarah,Kimberly,Deborah,Jessica,Shirley,Cynthia,Angela,Melissa,Brenda,Amy,Anna,Rebecca,Virginia,Kathleen,Pamela,Martha,Debra,Amanda,Stephanie,Carolyn,Christine,Marie,Janet,Catherine,Frances,Ann,Joyce,Diane' );
		$all = array_merge( $m, $fm );

		shuffle( $m );
		shuffle( $fm );
		shuffle( $all );

		switch ( $gender ) {
			default:
				return( current( $all ) );
			case 'm':
				return( current( $m ) );
			case 'fm':
				return( current( $fm ) );
		}
	}

	public function lastname() {
		$list = explode( ',', 'Smith,Johnson,Williams,Jones,Brown,Davis,Miller,Wilson,Moore,Taylor,Anderson,Thomas,Jackson,White,Harris,Martin,Thompson,Garcia,Martinez,Robinson,Clark,Rodriguez,Lewis,Lee,Walker,Hall,Allen,Young,Hernandez,King,Wright,Lopez,Hill,Scott,Green,Adams,Baker,Gonzalez,Nelson,Carter,Mitchell,Perez,Roberts,Turner,Phillips,Campbell,Parker,Evans,Edwards,Collins,Dexter,Dennis,Menace,Jesus,Bart,Homer,Dan,Bub,Sparkster,Yanni' );
		shuffle( $list );
		return( current( $list ) );
	}

	public function name() {
		$f = $this->firstname();
		$l = $this->lastname();
		return( $f . ' ' . $l );
	}

	public function username() {
		$n = rand( 1, 300 );
		$f = substr( $this->firstname(), 0, 1 );
		$l = $this->lastname() . $n;

		return( strtolower( $f . $l ) );
	}

	public function email( $u = false ) {
		if ( ! $u ) {
			$u = $this->username(); }
		$m = [ '@gmail.com', '@hotmail.com', '@yahoo.com', '@aol.com', '@redlinederby.com' ];
		shuffle( $m );
		$m = current( $m );

		return( strtolower( $u . $m ) );
	}

	public function street() {
		$list = explode( ',', 'Second,Third,First,Fourth,Park,Fifth,Main,Sixth,Oak,Seventh,Pine,Maple,Cedar,Eighth,Elm,Washington,Ninth,Lake,Hill' );
		$n    = rand( 700, 5500 );
		shuffle( $list );
		return( $n . ' ' . current( $list ) . ' St.' );
	}

	public function city() {
		$list = explode( ',', 'Gotham City,Metropolis,Oz,Smallville,Mos Eisley,Mayberry,Bedrock,Springfield,Vice City,Atlantis,Hill Valley,New New York,Cloud City,Bikini Bottom' );
		shuffle( $list );
		return( current( $list ) );
	}

	public function state( $full = false ) {
		$list = [
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District Of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
		];
		$abbv = array_rand( $list, 1 );

		if ( $full ) {
			return( $list[ $abbv ] );
		} else {
			return( $abbv );
		}
	}

	public function zipcode() {
		return( rand( 12345, 98765 ) );
	}

	public function address() {
		$a = $this->street() . '<br />' . $this->city() . ', ' . $this->state() . ' ' . $this->zipcode();
		return( $a );
	}

	public function telephone() {
		return( rand( 123, 876 ) . '-' . rand( 123, 987 ) . '-' . rand( 1234, 9876 ) );
	}
}
