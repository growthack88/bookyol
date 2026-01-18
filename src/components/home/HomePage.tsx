import { useState } from 'react';
import Header from './Header';
import SearchHero from './SearchHero';
import CategoryRow from './CategoryRow';
import HookCards from './HookCards';
import BookGrid from './BookGrid';
import AuthorStrip from './AuthorStrip';
import CollectionsGrid from './CollectionsGrid';
import QuoteBlock from './QuoteBlock';
import LatestTrending from './LatestTrending';
import Footer from './Footer';
import SearchModal from './SearchModal';

const HomePage = () => {
  const [isSearchOpen, setIsSearchOpen] = useState(false);

  return (
    <div className="min-h-screen bg-background">
      {/* Background pattern */}
      <div className="fixed inset-0 -z-10 bg-gradient-to-bl from-primary/3 via-background to-secondary/5" />
      <div 
        className="fixed inset-0 -z-10 opacity-[0.015]"
        style={{
          backgroundImage: `radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0)`,
          backgroundSize: '24px 24px',
        }}
      />

      <Header onSearchClick={() => setIsSearchOpen(true)} />
      
      <main>
        <SearchHero />
        <CategoryRow />
        <HookCards />
        <BookGrid />
        <AuthorStrip />
        <CollectionsGrid />
        <QuoteBlock />
        <LatestTrending />
      </main>

      <Footer />

      <SearchModal isOpen={isSearchOpen} onClose={() => setIsSearchOpen(false)} />
    </div>
  );
};

export default HomePage;
