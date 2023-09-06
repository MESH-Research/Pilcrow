<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Publication::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->company(),
            'is_publicly_visible' => true,
            'home_page_content' => $this->makeHomePageContent(),
            'new_submission_content' => $this->makeNewSubmissionContent(),
        ];
    }

    /**
     * Factory State for public visibility
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function hidden()
    {
        return $this->state(function (array $_) {
            return [
                'is_publicly_visible' => false,
            ];
        });
    }

    /**
     * @return string
     */
    private function makeHomePageContent()
    {
        return "<h3>Publication Home Page</h3> <p>This is an example of a publication's home page content. " .
            'This is displayed to users who view the home page of a publication. </p>' .
            "<p>This is usually a good place to describe a publication's focus, goals, team, etc. </p> " .
            '<p>This can be written by someone managing the publication by performing the following steps:</p> <ol>' .
            '<li>Log in as a user with sufficient publication-managing privileges. <ul>' .
                '<li>This can be an Application Admin or a Publication Admin of a publication. </li></ul></li>' .
            '<li>Visit the Publications page. </li>' .
            '<li>From the list of publications, select the publication you wish to modify. </li>' .
            "<li>Click the 'Configure Publication' button. </li>" .
            "<li>Click the 'Page Content' menu item. </li>" .
            "<li>Select the 'Home Page' item from the drop down menu. </li>" .
            '<li>Edit the text in the text area. </li>' .
            "<li>When finished, click the 'Save' button. </li>" .
            '</ol><p>Page content will be rendered as HTML. </p>';
    }

    /**
     * @return string
     */
    private function makeNewSubmissionContent()
    {
        return "<p>This is an example of content from the publication that's displayed to users when " .
            'they start the creation process for a new submission. This gives the publication an opportunity ' .
            'to brief the user with guidelines and expectations for the peer review process. </p>';
    }
}
