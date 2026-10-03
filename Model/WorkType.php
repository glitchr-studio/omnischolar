<?php

namespace Omnischolar\Model;

/** What a work is, in the few kinds a publication list sorts by. */
enum WorkType: string
{
    /** A journal article. */
    case ARTICLE = 'article';
    /** A book, or a whole edited volume. */
    case BOOK = 'book';
    /** A chapter of a book, an encyclopedia entry. */
    case CHAPTER = 'chapter';
    /** A conference paper, a talk, a poster: a "communication". */
    case CONFERENCE = 'conference';
    /** A preprint, a working paper. */
    case PREPRINT = 'preprint';
    /** A doctoral thesis, a habilitation, a dissertation. */
    case THESIS = 'thesis';
    case REPORT = 'report';
    case DATASET = 'dataset';
    case SOFTWARE = 'software';
    /** A book review, a peer review (a review article is an ARTICLE). */
    case REVIEW = 'review';
    /** An editorial, a letter, an erratum, a cover. */
    case EDITORIAL = 'editorial';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::ARTICLE => 'Article',
            self::BOOK => 'Book',
            self::CHAPTER => 'Book chapter',
            self::CONFERENCE => 'Conference paper',
            self::PREPRINT => 'Preprint',
            self::THESIS => 'Thesis',
            self::REPORT => 'Report',
            self::DATASET => 'Dataset',
            self::SOFTWARE => 'Software',
            self::REVIEW => 'Review',
            self::EDITORIAL => 'Editorial',
            self::OTHER => 'Other',
        };
    }

    /** A Crossref / CSL / OpenAlex / ORCID type name ("journal-article", "book-chapter", "proceedings-article"...). */
    public static function fromName(?string $name): self
    {
        return match (strtolower(str_replace(['_', ' '], '-', (string) $name))) {
            'article', 'journal-article', 'article-journal', 'magazine-article', 'newspaper-article', 'article-magazine', 'article-newspaper', 'letter-to-editor', 'review', 'review-article' => self::ARTICLE,
            'book', 'monograph', 'edited-book', 'reference-book', 'book-set', 'book-series', 'proceedings', 'edited-volume', 'textbook' => self::BOOK,
            'book-chapter', 'chapter', 'book-section', 'book-part', 'reference-entry', 'entry', 'entry-encyclopedia', 'entry-dictionary', 'encyclopedia-entry', 'dictionary-entry' => self::CHAPTER,
            'proceedings-article', 'conference-paper', 'paper-conference', 'conference-abstract', 'conference-poster', 'conference-presentation', 'conference-output', 'lecture-speech', 'speech', 'presentation', 'poster' => self::CONFERENCE,
            'preprint', 'posted-content', 'working-paper', 'article-preprint' => self::PREPRINT,
            'dissertation', 'thesis', 'dissertation-thesis', 'phdthesis' => self::THESIS,
            'report', 'report-component', 'report-series', 'technical-standard', 'standard', 'research-tool' => self::REPORT,
            'dataset', 'data-set', 'database', 'supplementary-materials', 'data-management-plan' => self::DATASET,
            'software', 'software-code' => self::SOFTWARE,
            'book-review', 'peer-review', 'reviewed-book' => self::REVIEW,
            'editorial', 'letter', 'erratum', 'retraction', 'paratext' => self::EDITORIAL,
            default => self::OTHER,
        };
    }
}
