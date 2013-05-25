<?php
class Controller_Docs_Base extends Controller
{
    /**
     * @var View
     */
    protected $view;

	public function before()
	{
	    // Use customized Markdown parser
	    define( 'MARKDOWN_PARSER_CLASS', 'Docs_Markdown' );

	    if ( ! function_exists( 'Markdown' ) )
	    {
	        // Load Markdown support
	        require Core::find_file( 'markdown', 'markdown' );
	    }

        $this->view = new View( 'docs/frame_view' );

        ob_start();
	}

    public function after()
    {
        $data = ob_get_clean();
        $this->view->centerhtml = $data;
        $this->view->render( true );
    }
}